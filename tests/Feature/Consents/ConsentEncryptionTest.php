<?php

use App\Enums\UserRole;
use App\Models\Consent;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('cifra el texto clínico del consentimiento en reposo', function () {
    $doctor = User::factory()->role(UserRole::Doctor)->create();
    $patient = Patient::factory()->create();
    $this->actingAs($doctor);

    $this->post(route('patients.consents.store', $patient), consentPayload([
        'diagnosis' => 'Diagnóstico confidencial del paciente',
        'treatment_plan' => 'Tratamiento confidencial propuesto',
        'risks_complications' => 'Riesgos confidenciales detallados',
    ]));

    $raw = DB::table('consents')->where('patient_id', $patient->id)->first();
    expect($raw->diagnosis)->not->toContain('confidencial')
        ->and($raw->treatment_plan)->not->toContain('confidencial')
        ->and($raw->risks_complications)->not->toContain('confidencial');

    $consent = Consent::firstWhere('patient_id', $patient->id);
    expect($consent->diagnosis)->toBe('Diagnóstico confidencial del paciente');
});

test('cifra el motivo de anulación en reposo', function () {
    $admin = User::factory()->role(UserRole::Administrador)->create();
    $consent = Consent::factory()->signed()->create();

    $this->actingAs($admin)->put(route('consents.void', $consent), [
        'void_reason' => 'Motivo confidencial de la anulación del expediente',
    ]);

    $raw = DB::table('consents')->where('id', $consent->id)->first();
    expect($raw->void_reason)->not->toContain('confidencial');
    expect($consent->fresh()->void_reason)->toContain('confidencial');
});
