<?php

use App\Enums\UserRole;
use App\Models\OdontogramRecord;
use App\Models\Patient;
use App\Models\User;
use App\Policies\OdontogramRecordPolicy;

dataset('roles_con_acceso_completo', [
    'doctor' => [UserRole::Doctor],
    'administrador' => [UserRole::Administrador],
    'superadmin' => [UserRole::Superadmin],
]);

test('un usuario de cualquier rol puede abrir el odontograma de un paciente', function (UserRole $role) {
    $user = User::factory()->role($role)->create();
    $patient = Patient::factory()->create();

    $this->actingAs($user)
        ->get(route('patients.odontogram', $patient))
        ->assertOk();
})->with('roles_con_acceso_completo');

test('abrir el odontograma se audita como acceso al expediente', function () {
    $user = User::factory()->role(UserRole::Doctor)->create();
    $patient = Patient::factory()->create();

    $this->actingAs($user)->get(route('patients.odontogram', $patient))->assertOk();

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $user->id,
        'patient_id' => $patient->id,
        'action' => 'viewed',
        'auditable_type' => Patient::class,
        'auditable_id' => $patient->id,
    ]);
});

test('un visitante no autenticado no puede abrir el odontograma', function () {
    $patient = Patient::factory()->create();

    $this->get(route('patients.odontogram', $patient))->assertRedirect(route('login'));
});

test('solo superadmin puede eliminar permanentemente un hallazgo del odontograma (forceDelete)', function () {
    $doctor = User::factory()->role(UserRole::Doctor)->create();
    $administrador = User::factory()->role(UserRole::Administrador)->create();
    $superadmin = User::factory()->role(UserRole::Superadmin)->create();
    $record = OdontogramRecord::factory()->create();

    expect($doctor->can('forceDelete', $record))->toBeFalse()
        ->and($administrador->can('forceDelete', $record))->toBeFalse()
        ->and($superadmin->can('forceDelete', $record))->toBeTrue();
});

test('el odontograma no tiene acción update: un hallazgo no se edita en sitio', function () {
    $doctor = User::factory()->role(UserRole::Doctor)->create();
    $record = OdontogramRecord::factory()->create();

    expect(method_exists(OdontogramRecordPolicy::class, 'update'))->toBeFalse()
        ->and($doctor->can('delete', $record))->toBeTrue()
        ->and($doctor->can('create', OdontogramRecord::class))->toBeTrue();
});
