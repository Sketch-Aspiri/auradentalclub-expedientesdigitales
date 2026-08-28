<?php

use App\Enums\UserRole;
use App\Models\Consent;
use App\Models\Consultation;
use App\Models\MedicalHistory;
use App\Models\OdontogramRecord;
use App\Models\Patient;
use App\Models\User;
use App\Support\SignatureImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

test('el borrado permanente de un paciente purga sus registros clínicos y audita cada uno', function () {
    // Arrange
    Storage::fake('local');
    $superadmin = User::factory()->role(UserRole::Superadmin)->create();
    $this->actingAs($superadmin);
    $patient = Patient::factory()->create();
    $history = MedicalHistory::factory()->for($patient)->create();
    $activeConsultation = Consultation::factory()->for($patient)->create();
    $trashedConsultation = Consultation::factory()->for($patient)->create();
    $trashedConsultation->delete();
    $odontogramRecord = OdontogramRecord::factory()->for($patient)->create();

    $activeConsent = Consent::factory()->for($patient)->create();
    $signaturePath = SignatureImage::store(fakeSignatureDataUrl(), "consents/{$activeConsent->id}/signatures");
    $activeConsent->forceFill(['signed_at' => now(), 'patient_signature_path' => $signaturePath])->save();
    $trashedConsent = Consent::factory()->for($patient)->create();
    $trashedConsent->delete();

    // Act
    $patient->forceDelete();

    // Assert — todo se purgó físicamente
    $this->assertDatabaseMissing('patients', ['id' => $patient->id]);
    $this->assertDatabaseMissing('medical_histories', ['id' => $history->id]);
    $this->assertDatabaseMissing('consultations', ['id' => $activeConsultation->id]);
    $this->assertDatabaseMissing('consultations', ['id' => $trashedConsultation->id]);
    $this->assertDatabaseMissing('odontogram_records', ['id' => $odontogramRecord->id]);
    $this->assertDatabaseMissing('consents', ['id' => $activeConsent->id]);
    $this->assertDatabaseMissing('consents', ['id' => $trashedConsent->id]);
    expect(SignatureImage::exists($signaturePath))->toBeFalse();

    // Assert — cada purga quedó registrada en audit_logs con el patient_id correcto
    foreach ([
        [Patient::class, $patient->id],
        [MedicalHistory::class, $history->id],
        [Consultation::class, $activeConsultation->id],
        [Consultation::class, $trashedConsultation->id],
        [OdontogramRecord::class, $odontogramRecord->id],
        [Consent::class, $activeConsent->id],
        [Consent::class, $trashedConsent->id],
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

test('el borrado permanente no deja registros huérfanos en ninguna tabla con patient_id', function () {
    // Contrato: cada tabla clínica que referencia a un paciente debe purgarse en
    // Patient::forceDeleting(). Este test descubre esas tablas dinámicamente, así que
    // fallará cuando un sprint futuro añada una relación sin conectarla al hook.
    $superadmin = User::factory()->role(UserRole::Superadmin)->create();
    $this->actingAs($superadmin);
    $patient = Patient::factory()->create();
    MedicalHistory::factory()->for($patient)->create();
    Consultation::factory()->for($patient)->count(2)->create();

    $patient->forceDelete();

    // audit_logs se excluye a propósito: conserva el rastro histórico (sin FK, CLAUDE.md §5).
    $tables = collect(DB::select(
        "SELECT DISTINCT TABLE_NAME as t FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND COLUMN_NAME = 'patient_id'"
    ))->pluck('t')->reject(fn ($table) => $table === 'audit_logs');

    expect($tables)->not->toBeEmpty();

    foreach ($tables as $table) {
        $orphans = DB::table($table)->where('patient_id', $patient->id)->count();
        $this->assertSame(0, $orphans, "La tabla [{$table}] quedó con registros huérfanos tras el purgado del paciente. Añade su relación a Patient::forceDeleting().");
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
