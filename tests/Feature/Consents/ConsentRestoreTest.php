<?php

use App\Enums\UserRole;
use App\Models\Consent;
use App\Models\Patient;
use App\Models\User;

test('un usuario de cualquier rol puede restaurar un consentimiento archivado', function (UserRole $role) {
    $user = User::factory()->role($role)->create();
    $consent = Consent::factory()->create();
    $consent->delete();

    $response = $this->actingAs($user)->put(route('consents.restore', $consent));

    $response->assertRedirect(route('patients.consents.index', $consent->patient_id));
    expect($consent->fresh()->trashed())->toBeFalse();
})->with([
    'doctor' => [UserRole::Doctor],
    'administrador' => [UserRole::Administrador],
    'superadmin' => [UserRole::Superadmin],
]);

test('restaurar un consentimiento deja rastro en audit_logs con el patient_id correcto', function () {
    $doctor = User::factory()->role(UserRole::Doctor)->create();
    $patient = Patient::factory()->create();
    $consent = Consent::factory()->for($patient)->create();
    $consent->delete();

    $this->actingAs($doctor)->put(route('consents.restore', $consent));

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $doctor->id,
        'patient_id' => $patient->id,
        'action' => 'restored',
        'auditable_type' => Consent::class,
        'auditable_id' => $consent->id,
    ]);
});

test('un visitante no autenticado no puede restaurar un consentimiento', function () {
    $consent = Consent::factory()->create();
    $consent->delete();

    $this->put(route('consents.restore', $consent))->assertRedirect(route('login'));
    expect($consent->fresh()->trashed())->toBeTrue();
});
