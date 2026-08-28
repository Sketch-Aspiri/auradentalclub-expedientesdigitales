<?php

use App\Enums\UserRole;
use App\Models\Consent;
use App\Models\Patient;
use App\Models\User;
use App\Support\SignatureImage;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
});

function signedConsentWithFiles(): Consent
{
    $consent = Consent::factory()->create();
    $consent->forceFill([
        'signed_at' => now(),
        'patient_signature_path' => SignatureImage::store(fakeSignatureDataUrl(), "consents/{$consent->id}/signatures"),
        'doctor_signature_path' => SignatureImage::store(fakeSignatureDataUrl(), "consents/{$consent->id}/signatures"),
    ])->save();

    return $consent;
}

test('la firma se sirve a un usuario autorizado con cabeceras que impiden el cacheo', function () {
    $doctor = User::factory()->role(UserRole::Doctor)->create();
    $consent = signedConsentWithFiles();

    $response = $this->actingAs($doctor)->get(route('consents.signature', [$consent, 'patient']));

    $response->assertOk();
    expect($response->headers->get('Cache-Control'))->toContain('no-store');
    expect($response->headers->get('X-Content-Type-Options'))->toBe('nosniff');
});

test('un visitante no autenticado no puede descargar una firma', function () {
    $consent = signedConsentWithFiles();

    $this->get(route('consents.signature', [$consent, 'patient']))->assertRedirect(route('login'));
});

test('devuelve 404 para una parte inexistente o sin firma', function () {
    $doctor = User::factory()->role(UserRole::Doctor)->create();
    $consent = signedConsentWithFiles();

    $this->actingAs($doctor)->get(route('consents.signature', [$consent, 'witness2']))->assertNotFound();
    $this->actingAs($doctor)->get(route('consents.signature', [$consent, 'otro']))->assertNotFound();
});

test('descargar una firma deja rastro de acceso al expediente en audit_logs', function () {
    $doctor = User::factory()->role(UserRole::Doctor)->create();
    $patient = Patient::factory()->create();
    $consent = Consent::factory()->for($patient)->create();
    $consent->forceFill([
        'signed_at' => now(),
        'patient_signature_path' => SignatureImage::store(fakeSignatureDataUrl(), "consents/{$consent->id}/signatures"),
    ])->save();

    $this->actingAs($doctor)->get(route('consents.signature', [$consent, 'patient']));

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $doctor->id,
        'patient_id' => $patient->id,
        'action' => 'viewed',
        'auditable_type' => Patient::class,
        'auditable_id' => $patient->id,
    ]);
});
