<?php

use App\Enums\UserRole;
use App\Models\Consultation;
use App\Models\MedicalHistory;
use App\Models\Patient;
use App\Models\User;

test('el borrado permanente de un paciente purga sus registros clínicos y audita cada uno', function () {
    // Arrange
    $superadmin = User::factory()->role(UserRole::Superadmin)->create();
    $this->actingAs($superadmin);
    $patient = Patient::factory()->create();
    $history = MedicalHistory::factory()->for($patient)->create();
    $activeConsultation = Consultation::factory()->for($patient)->create();
    $trashedConsultation = Consultation::factory()->for($patient)->create();
    $trashedConsultation->delete();

    // Act
    $patient->forceDelete();

    // Assert — todo se purgó físicamente
    $this->assertDatabaseMissing('patients', ['id' => $patient->id]);
    $this->assertDatabaseMissing('medical_histories', ['id' => $history->id]);
    $this->assertDatabaseMissing('consultations', ['id' => $activeConsultation->id]);
    $this->assertDatabaseMissing('consultations', ['id' => $trashedConsultation->id]);

    // Assert — cada purga quedó registrada en audit_logs con el patient_id correcto
    foreach ([
        [Patient::class, $patient->id],
        [MedicalHistory::class, $history->id],
        [Consultation::class, $activeConsultation->id],
        [Consultation::class, $trashedConsultation->id],
    ] as [$type, $id]) {
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $superadmin->id,
            'patient_id' => $patient->id,
            'action' => 'deleted',
            'auditable_type' => $type,
            'auditable_id' => $id,
        ]);
    }
});

test('el soft delete de un paciente no toca sus consultas', function () {
    // Arrange
    $admin = User::factory()->role(UserRole::Administrador)->create();
    $patient = Patient::factory()->create();
    $consultation = Consultation::factory()->for($patient)->create();

    // Act
    $this->actingAs($admin)->delete(route('patients.destroy', $patient));

    // Assert
    $this->assertSoftDeleted($patient);
    $this->assertDatabaseHas('consultations', ['id' => $consultation->id, 'deleted_at' => null]);
});
