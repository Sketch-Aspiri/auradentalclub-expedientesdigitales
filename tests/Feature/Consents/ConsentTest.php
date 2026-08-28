<?php

use App\Enums\UserRole;
use App\Models\Consent;
use App\Models\MedicalHistory;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('flujo completo: un doctor crea, ve, edita y elimina un consentimiento con auditoría', function () {
    $doctor = User::factory()->role(UserRole::Doctor)->create();
    $patient = Patient::factory()->create();
    $this->actingAs($doctor);

    // Alta.
    $this->post(route('patients.consents.store', $patient), consentPayload());

    $consent = Consent::firstWhere('patient_id', $patient->id);
    expect($consent)->not->toBeNull()
        ->and($consent->doctor_id)->toBe($doctor->id)
        ->and($consent->isDraft())->toBeTrue();

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $doctor->id,
        'patient_id' => $patient->id,
        'action' => 'created',
        'auditable_type' => Consent::class,
        'auditable_id' => $consent->id,
    ]);
    // El snapshot de salud se asigna antes del insert: un único `created`, sin `updated` espurio.
    $this->assertDatabaseMissing('audit_logs', [
        'action' => 'updated',
        'auditable_type' => Consent::class,
        'auditable_id' => $consent->id,
    ]);

    // Ver.
    $this->get(route('consents.show', $consent))->assertOk()->assertSee('Caries en órgano dentario 36');
    $this->assertDatabaseHas('audit_logs', [
        'patient_id' => $patient->id,
        'action' => 'viewed',
        'auditable_type' => Consent::class,
        'auditable_id' => $consent->id,
    ]);

    // Editar (borrador).
    $this->put(route('consents.update', $consent), consentPayload([
        'diagnosis' => 'Diagnóstico corregido',
    ]))->assertRedirect(route('consents.show', $consent));

    $consent->refresh();
    expect($consent->diagnosis)->toBe('Diagnóstico corregido');

    $this->assertDatabaseHas('audit_logs', [
        'patient_id' => $patient->id,
        'action' => 'updated',
        'auditable_type' => Consent::class,
        'auditable_id' => $consent->id,
    ]);

    // Eliminar (soft).
    $this->delete(route('consents.destroy', $consent));
    $this->assertSoftDeleted($consent);
    $this->assertDatabaseHas('audit_logs', [
        'patient_id' => $patient->id,
        'action' => 'deleted',
        'auditable_type' => Consent::class,
        'auditable_id' => $consent->id,
    ]);
});

test('anular un consentimiento firmado lo marca como anulado y lo audita', function () {
    $admin = User::factory()->role(UserRole::Administrador)->create();
    $patient = Patient::factory()->create();
    $consent = Consent::factory()->for($patient)->signed()->create();

    $this->actingAs($admin)->put(route('consents.void', $consent), [
        'void_reason' => 'Se capturó el diente equivocado, se reemplaza por uno nuevo.',
    ])->assertRedirect(route('consents.show', $consent));

    $consent->refresh();
    expect($consent->isVoided())->toBeTrue()
        ->and($consent->voided_by)->toBe($admin->id)
        ->and($consent->void_reason)->toContain('diente equivocado');

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $admin->id,
        'patient_id' => $patient->id,
        'action' => 'voided',
        'auditable_type' => Consent::class,
        'auditable_id' => $consent->id,
    ]);
    $this->assertDatabaseMissing('audit_logs', [
        'action' => 'updated',
        'auditable_type' => Consent::class,
        'auditable_id' => $consent->id,
    ]);
});

test('abrir el formulario de un consentimiento audita el acceso al expediente', function () {
    $doctor = User::factory()->role(UserRole::Doctor)->create();
    $patient = Patient::factory()->create();
    $this->actingAs($doctor);

    $this->get(route('patients.consents.create', $patient))->assertOk();

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $doctor->id,
        'patient_id' => $patient->id,
        'action' => 'viewed',
        'auditable_type' => Patient::class,
        'auditable_id' => $patient->id,
    ]);
});

test('al crear un consentimiento se copia una foto fija de la historia clínica del paciente', function () {
    $doctor = User::factory()->role(UserRole::Doctor)->create();
    $patient = Patient::factory()->create();
    MedicalHistory::factory()->for($patient)->create([
        'allergies' => 'Penicilina',
        'is_pregnant' => false,
        'prolonged_bleeding_history' => true,
    ]);
    $this->actingAs($doctor);

    $this->post(route('patients.consents.store', $patient), consentPayload());
    $consent = Consent::firstWhere('patient_id', $patient->id);

    expect($consent->health_snapshot)->toBeArray()
        ->and($consent->health_snapshot['allergies'])->toBe('Penicilina')
        ->and($consent->health_snapshot['prolonged_bleeding_history'])->toBeTrue();

    // Editar la historia clínica después NO cambia la copia del consentimiento.
    $patient->medicalHistory->update(['allergies' => 'Ninguna']);
    expect($consent->fresh()->health_snapshot['allergies'])->toBe('Penicilina');

    // Y la foto de salud queda cifrada en reposo.
    $raw = DB::table('consents')->where('id', $consent->id)->value('health_snapshot');
    expect($raw)->not->toContain('Penicilina');
});

test('el historial de consentimientos separa los archivados de los activos', function () {
    $doctor = User::factory()->role(UserRole::Doctor)->create();
    $patient = Patient::factory()->create();
    Consent::factory()->for($patient)->create();
    $archived = Consent::factory()->for($patient)->create();
    $archived->delete();

    $this->actingAs($doctor)->get(route('patients.consents.index', $patient))
        ->assertOk()
        ->assertSee('Consentimientos archivados');
});
