<?php

use App\Enums\UserRole;
use App\Models\Consent;
use App\Models\Patient;
use App\Models\User;

test('rechaza un consentimiento sin los campos clínicos obligatorios', function () {
    $doctor = User::factory()->role(UserRole::Doctor)->create();
    $patient = Patient::factory()->create();

    $response = $this->actingAs($doctor)->post(route('patients.consents.store', $patient), [
        'type' => 'general',
        'given_by' => 'paciente',
    ]);

    $response->assertSessionHasErrors(['treatment_plan', 'risks_complications']);
    $this->assertDatabaseCount('consents', 0);
});

test('exige el parentesco cuando no firma el propio paciente', function () {
    $doctor = User::factory()->role(UserRole::Doctor)->create();
    $patient = Patient::factory()->create();

    $this->actingAs($doctor)->post(route('patients.consents.store', $patient), consentPayload([
        'given_by' => 'familiar',
        'relationship' => '',
    ]))->assertSessionHasErrors('relationship');

    $this->actingAs($doctor)->post(route('patients.consents.store', $patient), consentPayload([
        'given_by' => 'familiar',
        'relationship' => 'Madre',
    ]))->assertSessionHasNoErrors();
});

test('un doctor no puede asignar el consentimiento a otro doctor', function () {
    $doctor = User::factory()->role(UserRole::Doctor)->create();
    $otherDoctor = User::factory()->role(UserRole::Doctor)->create();
    $patient = Patient::factory()->create();

    $this->actingAs($doctor)->post(route('patients.consents.store', $patient), consentPayload([
        'doctor_id' => $otherDoctor->id,
    ]));

    expect(Consent::firstWhere('patient_id', $patient->id)->doctor_id)->toBe($doctor->id);
});

test('un administrador debe elegir un doctor válido', function () {
    $admin = User::factory()->role(UserRole::Administrador)->create();
    $patient = Patient::factory()->create();

    $this->actingAs($admin)->post(route('patients.consents.store', $patient), consentPayload())
        ->assertSessionHasErrors('doctor_id');

    $this->actingAs($admin)->post(route('patients.consents.store', $patient), consentPayload([
        'doctor_id' => $admin->id, // no es doctor
    ]))->assertSessionHasErrors('doctor_id');
});

test('la anulación exige un motivo', function () {
    $admin = User::factory()->role(UserRole::Administrador)->create();
    $consent = Consent::factory()->signed()->create();

    $this->actingAs($admin)->put(route('consents.void', $consent), ['void_reason' => ''])
        ->assertSessionHasErrors('void_reason');

    expect($consent->fresh()->isVoided())->toBeFalse();
});
