<?php

use App\Enums\UserRole;
use App\Models\Patient;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->role(UserRole::Doctor)->create());
    $this->patient = Patient::factory()->create();
    $this->validPayload = [
        'has_diabetes' => '0',
        'has_hypertension' => '0',
        'has_heart_disease' => '0',
        'has_hiv_hepatitis' => '0',
        'has_coagulation_problems' => '0',
        'has_seizures' => '0',
        'has_been_hospitalized_or_operated' => '0',
        'smokes' => '0',
        'drinks_alcohol' => '0',
        'prolonged_bleeding_history' => '0',
        'weight_loss_products_history' => '0',
    ];
});

test('guarda la historia clínica con datos válidos', function () {
    // Act
    $response = $this->put(
        route('patients.medical-history.update', $this->patient),
        [...$this->validPayload, 'allergies' => 'Penicilina', 'oral_hygiene_times_per_day' => 2]
    );

    // Assert
    $response->assertRedirect(route('patients.medical-history.show', $this->patient));
    $this->assertDatabaseHas('medical_histories', ['patient_id' => $this->patient->id]);
});

test('rechaza un valor no booleano en un antecedente patológico', function () {
    // Act
    $response = $this->put(
        route('patients.medical-history.update', $this->patient),
        [...$this->validPayload, 'has_diabetes' => 'tal-vez']
    );

    // Assert
    $response->assertSessionHasErrors('has_diabetes');
});

test('rechaza un número de higiene oral fuera de rango', function () {
    // Act
    $response = $this->put(
        route('patients.medical-history.update', $this->patient),
        [...$this->validPayload, 'oral_hygiene_times_per_day' => 999]
    );

    // Assert
    $response->assertSessionHasErrors('oral_hygiene_times_per_day');
});

test('rechaza el guardado si falta un antecedente patológico requerido', function () {
    // Arrange
    $payload = $this->validPayload;
    unset($payload['has_seizures']);

    // Act
    $response = $this->put(route('patients.medical-history.update', $this->patient), $payload);

    // Assert
    $response->assertSessionHasErrors('has_seizures');
});
