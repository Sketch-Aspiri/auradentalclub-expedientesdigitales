<?php

use App\Enums\UserRole;
use App\Livewire\Consents\SignConsent;
use App\Models\Consent;
use App\Models\Patient;
use App\Models\User;
use App\Support\SignatureImage;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('local');
});

test('firmar un consentimiento requiere la firma del paciente y del médico', function () {
    $doctor = User::factory()->role(UserRole::Doctor)->create();
    $this->actingAs($doctor);
    $consent = Consent::factory()->create();

    Livewire::test(SignConsent::class, ['consent' => $consent])
        ->call('submit')
        ->assertHasErrors(['patientSignature', 'doctorSignature']);

    expect($consent->fresh()->isDraft())->toBeTrue();
});

test('un nombre de testigo sin firma es un error', function () {
    $doctor = User::factory()->role(UserRole::Doctor)->create();
    $this->actingAs($doctor);
    $consent = Consent::factory()->create();

    Livewire::test(SignConsent::class, ['consent' => $consent])
        ->set('patientSignature', fakeSignatureDataUrl())
        ->set('doctorSignature', fakeSignatureDataUrl())
        ->set('witness1Name', 'Laura Méndez')
        ->call('submit')
        ->assertHasErrors('witness1Signature');
});

test('firmar correctamente guarda las firmas, marca signed_at y audita el evento signed', function () {
    $doctor = User::factory()->role(UserRole::Doctor)->create();
    $patient = Patient::factory()->create();
    $this->actingAs($doctor);
    $consent = Consent::factory()->for($patient)->create();

    Livewire::test(SignConsent::class, ['consent' => $consent])
        ->set('patientSignature', fakeSignatureDataUrl())
        ->set('doctorSignature', fakeSignatureDataUrl())
        ->set('witness1Name', 'Laura Méndez')
        ->set('witness1Signature', fakeSignatureDataUrl())
        ->call('submit')
        ->assertHasNoErrors()
        ->assertRedirect(route('consents.show', $consent));

    $consent->refresh();
    expect($consent->isSigned())->toBeTrue()
        ->and($consent->witness1_name)->toBe('Laura Méndez');

    foreach (['patient', 'doctor', 'witness1'] as $party) {
        expect(SignatureImage::exists($consent->signaturePaths()[$party]))->toBeTrue();
    }
    expect($consent->signaturePaths()['witness2'])->toBeNull();

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $doctor->id,
        'patient_id' => $patient->id,
        'action' => 'signed',
        'auditable_type' => Consent::class,
        'auditable_id' => $consent->id,
    ]);
    // Firmar es un evento `signed`, no un `updated` sobre un documento que pasa a inmutable.
    $this->assertDatabaseMissing('audit_logs', [
        'action' => 'updated',
        'auditable_type' => Consent::class,
        'auditable_id' => $consent->id,
    ]);
});

test('no se puede abrir la pantalla de firma de un consentimiento ya firmado', function () {
    $doctor = User::factory()->role(UserRole::Doctor)->create();
    $this->actingAs($doctor);
    $consent = Consent::factory()->signed()->create();

    Livewire::test(SignConsent::class, ['consent' => $consent])
        ->assertForbidden();
});
