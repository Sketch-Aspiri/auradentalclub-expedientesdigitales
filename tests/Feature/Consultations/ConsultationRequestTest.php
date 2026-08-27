<?php

use App\Enums\UserRole;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\User;

beforeEach(function () {
    $this->doctor = User::factory()->role(UserRole::Doctor)->create();
    $this->patient = Patient::factory()->create();
    $this->validPayload = [
        'consultation_date' => now()->format('Y-m-d'),
        'chief_complaint' => 'Dolor en zona posterior derecha',
        'clinical_diagnosis' => 'Pericoronitis del tercer molar',
        'treatment_plan' => 'Enjuagues y valoración quirúrgica',
    ];
});

test('un doctor registra una consulta con datos válidos y queda asignada a él', function () {
    $this->actingAs($this->doctor);

    $response = $this->post(route('patients.consultations.store', $this->patient), $this->validPayload);

    $response->assertRedirect();
    $this->assertDatabaseHas('consultations', [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
    ]);
});

test('rechaza la consulta cuando el diagnóstico clínico está vacío', function () {
    $this->actingAs($this->doctor);
    $payload = $this->validPayload;
    unset($payload['clinical_diagnosis']);

    $response = $this->post(route('patients.consultations.store', $this->patient), $payload);

    $response->assertSessionHasErrors('clinical_diagnosis');
    $this->assertDatabaseCount('consultations', 0);
});

test('rechaza la consulta cuando el motivo de consulta está vacío', function () {
    $this->actingAs($this->doctor);
    $payload = $this->validPayload;
    unset($payload['chief_complaint']);

    $this->post(route('patients.consultations.store', $this->patient), $payload)
        ->assertSessionHasErrors('chief_complaint');
});

test('rechaza una fecha de consulta futura', function () {
    $this->actingAs($this->doctor);

    $response = $this->post(route('patients.consultations.store', $this->patient), [
        ...$this->validPayload,
        'consultation_date' => now()->addDay()->format('Y-m-d'),
    ]);

    $response->assertSessionHasErrors('consultation_date');
});

test('rechaza signos vitales fuera de rango razonable', function () {
    $this->actingAs($this->doctor);

    $response = $this->post(route('patients.consultations.store', $this->patient), [
        ...$this->validPayload,
        'heart_rate' => 900,
        'temperature' => 'caliente',
    ]);

    $response->assertSessionHasErrors(['heart_rate', 'temperature']);
});

test('rechaza un nivel de higiene oral fuera del catálogo', function () {
    $this->actingAs($this->doctor);

    $this->post(route('patients.consultations.store', $this->patient), [
        ...$this->validPayload,
        'oral_hygiene_level' => 'pésima',
    ])->assertSessionHasErrors('oral_hygiene_level');
});

test('un administrador debe seleccionar un doctor tratante válido', function () {
    $admin = User::factory()->role(UserRole::Administrador)->create();
    $this->actingAs($admin);

    // Sin doctor_id
    $this->post(route('patients.consultations.store', $this->patient), $this->validPayload)
        ->assertSessionHasErrors('doctor_id');

    // Con un usuario que no es doctor
    $this->post(route('patients.consultations.store', $this->patient), [
        ...$this->validPayload,
        'doctor_id' => $admin->id,
    ])->assertSessionHasErrors('doctor_id');
});

test('un administrador registra la consulta a nombre del doctor elegido', function () {
    $admin = User::factory()->role(UserRole::Administrador)->create();
    $this->actingAs($admin);

    $this->post(route('patients.consultations.store', $this->patient), [
        ...$this->validPayload,
        'doctor_id' => $this->doctor->id,
    ]);

    $this->assertDatabaseHas('consultations', [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
    ]);
});

test('un doctor no reasigna la consulta a sí mismo al editar la de otro doctor', function () {
    $otherDoctor = User::factory()->role(UserRole::Doctor)->create();
    $consultation = Consultation::factory()->create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $otherDoctor->id,
    ]);
    $this->actingAs($this->doctor);

    $this->put(route('consultations.update', $consultation), [
        ...$this->validPayload,
        'doctor_id' => $this->doctor->id,
    ]);

    $this->assertDatabaseHas('consultations', [
        'id' => $consultation->id,
        'doctor_id' => $otherDoctor->id,
    ]);
});

test('un doctor no puede reasignar la consulta colando doctor_id por la query string', function () {
    $otherDoctor = User::factory()->role(UserRole::Doctor)->create();
    $consultation = Consultation::factory()->create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $otherDoctor->id,
    ]);
    $this->actingAs($this->doctor);

    // doctor_id inyectado en la URL, no en el body
    $this->put(
        route('consultations.update', $consultation).'?doctor_id='.$this->doctor->id,
        $this->validPayload,
    );

    $this->assertDatabaseHas('consultations', [
        'id' => $consultation->id,
        'doctor_id' => $otherDoctor->id,
    ]);
});
