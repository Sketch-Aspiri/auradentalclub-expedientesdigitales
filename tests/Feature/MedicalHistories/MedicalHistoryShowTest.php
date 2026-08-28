<?php

use App\Enums\UserRole;
use App\Models\MedicalHistory;
use App\Models\Patient;
use App\Models\User;

test('muestra las condiciones presentes y el estado vacío de una historia clínica existente', function () {
    // Arrange
    $user = User::factory()->role(UserRole::Doctor)->create();
    $medicalHistory = MedicalHistory::factory()->create([
        'has_diabetes' => true,
        'has_hypertension' => false,
        'has_heart_disease' => false,
        'has_hiv_hepatitis' => false,
        'has_coagulation_problems' => false,
        'has_seizures' => false,
        'prolonged_bleeding_history' => false,
        'weight_loss_products_history' => false,
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('patients.medical-history.show', $medicalHistory->patient));

    // Assert
    $response->assertOk()
        ->assertSee('Diabetes')
        ->assertSee('Sin antecedentes adicionales registrados.');
});

test('muestra un estado vacío con la acción de capturar cuando el paciente no tiene historia clínica', function () {
    // Arrange
    $user = User::factory()->role(UserRole::Administrador)->create();
    $patient = Patient::factory()->create();

    // Act
    $response = $this->actingAs($user)->get(route('patients.medical-history.show', $patient));

    // Assert
    $response->assertOk()
        ->assertSee('Este paciente aún no tiene historia clínica registrada.')
        ->assertSee(route('patients.medical-history.edit', $patient), false);
});

test('registra un audit_log de tipo viewed al consultar una historia clínica existente', function () {
    // Arrange
    $user = User::factory()->role(UserRole::Superadmin)->create();
    $medicalHistory = MedicalHistory::factory()->create();

    // Act
    $this->actingAs($user)->get(route('patients.medical-history.show', $medicalHistory->patient));

    // Assert
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $user->id,
        'patient_id' => $medicalHistory->patient_id,
        'action' => 'viewed',
        'auditable_type' => MedicalHistory::class,
        'auditable_id' => $medicalHistory->id,
    ]);
});

test('no registra audit_log viewed al consultar un paciente sin historia clínica todavía', function () {
    // Arrange
    $user = User::factory()->role(UserRole::Doctor)->create();
    $patient = Patient::factory()->create();

    // Act
    $this->actingAs($user)->get(route('patients.medical-history.show', $patient));

    // Assert
    $this->assertDatabaseMissing('audit_logs', [
        'patient_id' => $patient->id,
        'action' => 'viewed',
        'auditable_type' => MedicalHistory::class,
    ]);
});
