<?php

use App\Enums\UserRole;
use App\Models\Consent;
use App\Models\Patient;
use App\Models\User;

dataset('consent_roles', [
    'doctor' => [UserRole::Doctor],
    'administrador' => [UserRole::Administrador],
    'superadmin' => [UserRole::Superadmin],
]);

test('cualquier rol clínico puede ver el listado y el detalle de consentimientos', function (UserRole $role) {
    $user = User::factory()->role($role)->create();
    $patient = Patient::factory()->create();
    $consent = Consent::factory()->for($patient)->create();

    $this->actingAs($user)->get(route('patients.consents.index', $patient))->assertOk();
    $this->actingAs($user)->get(route('consents.show', $consent))->assertOk();
})->with('consent_roles');

test('cualquier rol clínico puede crear un consentimiento', function (UserRole $role) {
    $user = User::factory()->role($role)->create();
    $doctor = User::factory()->role(UserRole::Doctor)->create();
    $patient = Patient::factory()->create();

    $response = $this->actingAs($user)->post(
        route('patients.consents.store', $patient),
        consentPayload(['doctor_id' => $doctor->id]),
    );

    $response->assertRedirect();
    $this->assertDatabaseHas('consents', ['patient_id' => $patient->id]);
})->with('consent_roles');

test('un consentimiento en borrador se puede editar y eliminar', function (UserRole $role) {
    $user = User::factory()->role($role)->create();
    $consent = Consent::factory()->create();

    $this->actingAs($user)->get(route('consents.edit', $consent))->assertOk();
    $this->actingAs($user)->delete(route('consents.destroy', $consent))->assertRedirect();
    $this->assertSoftDeleted($consent);
})->with('consent_roles');

test('un consentimiento firmado no se puede editar ni eliminar', function () {
    $user = User::factory()->role(UserRole::Superadmin)->create();
    $consent = Consent::factory()->signed()->create();

    $this->actingAs($user)->get(route('consents.edit', $consent))->assertForbidden();
    $this->actingAs($user)->put(route('consents.update', $consent), consentPayload())->assertForbidden();
    $this->actingAs($user)->delete(route('consents.destroy', $consent))->assertForbidden();
    $this->actingAs($user)->get(route('consents.sign', $consent))->assertForbidden();
});

test('solo un consentimiento firmado se puede anular', function () {
    $admin = User::factory()->role(UserRole::Administrador)->create();

    $draft = Consent::factory()->create();
    expect($admin->can('void', $draft))->toBeFalse();

    $signed = Consent::factory()->signed()->create();
    expect($admin->can('void', $signed))->toBeTrue();

    $voided = Consent::factory()->voided()->create();
    expect($admin->can('void', $voided))->toBeFalse();
});

test('solo superadmin puede eliminar permanentemente un consentimiento', function () {
    $consent = Consent::factory()->create();

    expect(User::factory()->role(UserRole::Doctor)->create()->can('forceDelete', $consent))->toBeFalse()
        ->and(User::factory()->role(UserRole::Administrador)->create()->can('forceDelete', $consent))->toBeFalse()
        ->and(User::factory()->role(UserRole::Superadmin)->create()->can('forceDelete', $consent))->toBeTrue();
});

test('un visitante no autenticado no puede acceder a ninguna ruta de consentimiento', function () {
    $patient = Patient::factory()->create();
    $consent = Consent::factory()->for($patient)->create();

    $this->get(route('patients.consents.index', $patient))->assertRedirect(route('login'));
    $this->get(route('patients.consents.create', $patient))->assertRedirect(route('login'));
    $this->get(route('consents.show', $consent))->assertRedirect(route('login'));
    $this->get(route('consents.edit', $consent))->assertRedirect(route('login'));
    $this->get(route('consents.sign', $consent))->assertRedirect(route('login'));
    $this->get(route('consents.print', $consent))->assertRedirect(route('login'));
    $this->put(route('consents.update', $consent), consentPayload())->assertRedirect(route('login'));
    $this->delete(route('consents.destroy', $consent))->assertRedirect(route('login'));

    $this->assertDatabaseHas('consents', ['id' => $consent->id, 'deleted_at' => null]);
});
