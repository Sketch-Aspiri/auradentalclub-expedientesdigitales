<?php

use App\Enums\UserRole;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\User;

dataset('roles_con_acceso_completo', [
    'doctor' => [UserRole::Doctor],
    'administrador' => [UserRole::Administrador],
    'superadmin' => [UserRole::Superadmin],
]);

function consultationPayload(array $overrides = []): array
{
    return array_merge([
        'consultation_date' => now()->format('Y-m-d'),
        'chief_complaint' => 'Dolor al masticar',
        'clinical_diagnosis' => 'Caries en pieza 36',
        'treatment_plan' => 'Obturación',
    ], $overrides);
}

test('un usuario de cualquier rol puede ver el historial de consultas de un paciente', function (UserRole $role) {
    $user = User::factory()->role($role)->create();
    $patient = Patient::factory()->create();

    $response = $this->actingAs($user)->get(route('patients.consultations.index', $patient));

    $response->assertOk();
})->with('roles_con_acceso_completo');

test('un usuario de cualquier rol puede abrir el formulario de nueva consulta', function (UserRole $role) {
    $user = User::factory()->role($role)->create();
    $patient = Patient::factory()->create();

    $response = $this->actingAs($user)->get(route('patients.consultations.create', $patient));

    $response->assertOk();
})->with('roles_con_acceso_completo');

test('un usuario de cualquier rol puede registrar una consulta', function (UserRole $role) {
    $user = User::factory()->role($role)->create();
    $doctor = User::factory()->role(UserRole::Doctor)->create();
    $patient = Patient::factory()->create();

    $response = $this->actingAs($user)->post(
        route('patients.consultations.store', $patient),
        consultationPayload(['doctor_id' => $doctor->id]),
    );

    $response->assertRedirect();
    $this->assertDatabaseHas('consultations', ['patient_id' => $patient->id]);
})->with('roles_con_acceso_completo');

test('un usuario de cualquier rol puede ver y editar cualquier consulta', function (UserRole $role) {
    $user = User::factory()->role($role)->create();
    $consultation = Consultation::factory()->create();

    $this->actingAs($user)->get(route('consultations.show', $consultation))->assertOk();

    $response = $this->put(route('consultations.update', $consultation), consultationPayload([
        'clinical_diagnosis' => 'Diagnóstico corregido',
    ]));

    $response->assertRedirect(route('consultations.show', $consultation));
})->with('roles_con_acceso_completo');

test('un usuario de cualquier rol puede eliminar una consulta (soft delete)', function (UserRole $role) {
    $user = User::factory()->role($role)->create();
    $consultation = Consultation::factory()->create();

    $response = $this->actingAs($user)->delete(route('consultations.destroy', $consultation));

    $response->assertRedirect(route('patients.consultations.index', $consultation->patient_id));
    $this->assertSoftDeleted($consultation);
})->with('roles_con_acceso_completo');

test('un visitante no autenticado no puede ver el historial de consultas', function () {
    $patient = Patient::factory()->create();

    $this->get(route('patients.consultations.index', $patient))->assertRedirect(route('login'));
});

test('un visitante no autenticado no puede acceder a ninguna ruta de consulta individual', function () {
    $consultation = Consultation::factory()->create();

    $this->get(route('consultations.show', $consultation))->assertRedirect(route('login'));
    $this->get(route('consultations.edit', $consultation))->assertRedirect(route('login'));
    $this->put(route('consultations.update', $consultation), consultationPayload())->assertRedirect(route('login'));
    $this->delete(route('consultations.destroy', $consultation))->assertRedirect(route('login'));

    $this->assertDatabaseHas('consultations', ['id' => $consultation->id, 'deleted_at' => null]);
});

test('solo superadmin puede eliminar permanentemente una consulta (forceDelete)', function () {
    $doctor = User::factory()->role(UserRole::Doctor)->create();
    $administrador = User::factory()->role(UserRole::Administrador)->create();
    $superadmin = User::factory()->role(UserRole::Superadmin)->create();
    $consultation = Consultation::factory()->create();

    expect($doctor->can('forceDelete', $consultation))->toBeFalse()
        ->and($administrador->can('forceDelete', $consultation))->toBeFalse()
        ->and($superadmin->can('forceDelete', $consultation))->toBeTrue();
});
