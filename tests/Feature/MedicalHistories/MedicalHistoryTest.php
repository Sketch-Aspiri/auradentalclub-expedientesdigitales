<?php

use App\Enums\UserRole;
use App\Models\MedicalHistory;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('encripta los campos clínicos sensibles antes de persistirlos', function () {
    // Arrange
    $user = User::factory()->role(UserRole::Doctor)->create();
    $patient = Patient::factory()->create();
    $this->actingAs($user);

    // Act
    $this->put(route('patients.medical-history.update', $patient), [
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
        'allergies' => 'Penicilina y anestesia local',
    ]);

    // Assert
    $rawValue = DB::table('medical_histories')->where('patient_id', $patient->id)->value('allergies');
    expect($rawValue)->not->toBe('Penicilina y anestesia local')
        ->and($rawValue)->not->toContain('Penicilina');

    $medicalHistory = MedicalHistory::firstWhere('patient_id', $patient->id);
    expect($medicalHistory->allergies)->toBe('Penicilina y anestesia local');
});

test('solo existe una historia clínica por paciente: el segundo guardado actualiza, no duplica', function () {
    // Arrange
    $user = User::factory()->role(UserRole::Administrador)->create();
    $patient = Patient::factory()->create();
    $this->actingAs($user);
    $basePayload = [
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

    // Act
    $this->put(route('patients.medical-history.update', $patient), [...$basePayload, 'has_diabetes' => '1']);
    $this->put(route('patients.medical-history.update', $patient), [...$basePayload, 'has_diabetes' => '0']);

    // Assert
    $this->assertDatabaseCount('medical_histories', 1);
    $this->assertDatabaseHas('medical_histories', ['patient_id' => $patient->id, 'has_diabetes' => false]);
});

test('registra audit_logs al crear y al editar la historia clínica, con el patient_id correcto', function () {
    // Arrange
    $user = User::factory()->role(UserRole::Superadmin)->create();
    $patient = Patient::factory()->create();
    $this->actingAs($user);
    $payload = [
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

    // Act — primer guardado (crea)
    $this->put(route('patients.medical-history.update', $patient), $payload);
    $medicalHistory = MedicalHistory::firstWhere('patient_id', $patient->id);

    // Assert — created
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $user->id,
        'patient_id' => $patient->id,
        'action' => 'created',
        'auditable_type' => MedicalHistory::class,
        'auditable_id' => $medicalHistory->id,
    ]);

    // Act — ver
    $this->get(route('patients.medical-history.edit', $patient));

    // Assert — viewed
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $user->id,
        'patient_id' => $patient->id,
        'action' => 'viewed',
        'auditable_type' => MedicalHistory::class,
        'auditable_id' => $medicalHistory->id,
    ]);

    // Act — segundo guardado (actualiza)
    $this->put(route('patients.medical-history.update', $patient), [...$payload, 'has_diabetes' => '1']);

    // Assert — updated
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $user->id,
        'patient_id' => $patient->id,
        'action' => 'updated',
        'auditable_type' => MedicalHistory::class,
        'auditable_id' => $medicalHistory->id,
    ]);
});

test('no registra audit_log viewed al abrir el formulario de un paciente sin historia clínica todavía', function () {
    // Arrange
    $user = User::factory()->role(UserRole::Doctor)->create();
    $patient = Patient::factory()->create();
    $this->actingAs($user);

    // Act
    $this->get(route('patients.medical-history.edit', $patient));

    // Assert
    $this->assertDatabaseMissing('audit_logs', [
        'patient_id' => $patient->id,
        'action' => 'viewed',
        'auditable_type' => MedicalHistory::class,
    ]);
});
