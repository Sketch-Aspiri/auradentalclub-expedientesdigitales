<?php

use App\Enums\UserRole;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('flujo completo: un doctor registra, ve, edita y elimina una consulta con auditoría', function () {
    // Arrange
    $doctor = User::factory()->role(UserRole::Doctor)->create();
    $patient = Patient::factory()->create();
    $this->actingAs($doctor);
    $payload = [
        'consultation_date' => now()->format('Y-m-d'),
        'chief_complaint' => 'Sensibilidad al frío',
        'clinical_diagnosis' => 'Recesión gingival localizada',
        'treatment_plan' => 'Aplicación de barniz de flúor',
    ];

    // Act — alta
    $this->post(route('patients.consultations.store', $patient), $payload);
    $consultation = Consultation::firstWhere('patient_id', $patient->id);

    // Assert — alta + audit
    expect($consultation)->not->toBeNull()
        ->and($consultation->doctor_id)->toBe($doctor->id);
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $doctor->id,
        'patient_id' => $patient->id,
        'action' => 'created',
        'auditable_type' => Consultation::class,
        'auditable_id' => $consultation->id,
    ]);

    // Act — ver
    $this->get(route('consultations.show', $consultation))->assertOk()->assertSee('Recesión gingival localizada');

    // Assert — viewed
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $doctor->id,
        'patient_id' => $patient->id,
        'action' => 'viewed',
        'auditable_type' => Consultation::class,
        'auditable_id' => $consultation->id,
    ]);

    // Act — editar
    $this->put(route('consultations.update', $consultation), [...$payload, 'prognosis' => 'Favorable']);

    // Assert — updated
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $doctor->id,
        'patient_id' => $patient->id,
        'action' => 'updated',
        'auditable_type' => Consultation::class,
        'auditable_id' => $consultation->id,
    ]);

    // Act — eliminar
    $this->delete(route('consultations.destroy', $consultation));

    // Assert — deleted
    $this->assertSoftDeleted($consultation);
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $doctor->id,
        'patient_id' => $patient->id,
        'action' => 'deleted',
        'auditable_type' => Consultation::class,
        'auditable_id' => $consultation->id,
    ]);
});

test('cifra las notas clínicas en reposo', function () {
    $doctor = User::factory()->role(UserRole::Doctor)->create();
    $patient = Patient::factory()->create();
    $this->actingAs($doctor);

    $this->post(route('patients.consultations.store', $patient), [
        'consultation_date' => now()->format('Y-m-d'),
        'chief_complaint' => 'Motivo de prueba de cifrado',
        'clinical_diagnosis' => 'Diagnóstico confidencial del paciente',
        'treatment_plan' => 'Plan de tratamiento confidencial',
    ]);

    $raw = DB::table('consultations')->where('patient_id', $patient->id)->first();
    expect($raw->clinical_diagnosis)->not->toBe('Diagnóstico confidencial del paciente')
        ->and($raw->clinical_diagnosis)->not->toContain('confidencial')
        ->and($raw->chief_complaint)->not->toContain('cifrado');

    $consultation = Consultation::firstWhere('patient_id', $patient->id);
    expect($consultation->clinical_diagnosis)->toBe('Diagnóstico confidencial del paciente');
});

test('el historial de consultas se muestra en orden cronológico descendente', function () {
    $doctor = User::factory()->role(UserRole::Doctor)->create();
    $patient = Patient::factory()->create();

    Consultation::factory()->for($patient)->create([
        'consultation_date' => '2026-01-10',
        'clinical_diagnosis' => 'Diagnóstico de enero',
    ]);
    Consultation::factory()->for($patient)->create([
        'consultation_date' => '2026-06-15',
        'clinical_diagnosis' => 'Diagnóstico de junio',
    ]);
    Consultation::factory()->for($patient)->create([
        'consultation_date' => '2026-03-01',
        'clinical_diagnosis' => 'Diagnóstico de marzo',
    ]);

    $response = $this->actingAs($doctor)->get(route('patients.consultations.index', $patient));

    $response->assertOk()->assertSeeInOrder([
        'Diagnóstico de junio',
        'Diagnóstico de marzo',
        'Diagnóstico de enero',
    ]);
});

test('auditar el acceso al historial de consultas de un paciente', function () {
    $doctor = User::factory()->role(UserRole::Doctor)->create();
    $patient = Patient::factory()->create();
    Consultation::factory()->for($patient)->create();

    $this->actingAs($doctor)->get(route('patients.consultations.index', $patient));

    // Se audita como acceso al expediente del paciente (no como una consulta individual).
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $doctor->id,
        'patient_id' => $patient->id,
        'action' => 'viewed',
        'auditable_type' => Patient::class,
        'auditable_id' => $patient->id,
    ]);
    $this->assertDatabaseMissing('audit_logs', [
        'action' => 'viewed',
        'auditable_type' => Consultation::class,
    ]);
});
