<?php

namespace App\Livewire\Consents;

use App\Models\Consent;
use App\Support\SignatureImage;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Pantalla de firma de un consentimiento (Sprint 5). Los canvases (signature_pad) escriben su
 * dataURL PNG en las propiedades de firma vía Alpine; al enviar, cada firma se re-codifica y se
 * guarda en el disco privado `local` (App\Support\SignatureImage) y el consentimiento pasa a
 * "firmado" (inmutable). Firmar solo es posible sobre un borrador.
 */
class SignConsent extends Component
{
    #[Locked]
    public int $consentId;

    public string $patientSignature = '';

    public string $doctorSignature = '';

    public string $witness1Name = '';

    public string $witness1Signature = '';

    public string $witness2Name = '';

    public string $witness2Signature = '';

    public function mount(Consent $consent): void
    {
        $this->authorize('sign', $consent);
        abort_unless($consent->isDraft(), 403);

        $this->consentId = $consent->getKey();
    }

    public function submit(): void
    {
        $consent = Consent::findOrFail($this->consentId);

        $this->authorize('sign', $consent);
        abort_unless($consent->isDraft(), 403);

        // Tope de tamaño del dataURL (~1.5 MB decodificado) antes de tocar GD — evita una bomba
        // de descompresión / dimensiones desde una sesión con permiso `sign`.
        $sizeCap = ['max:2000000'];

        $this->validate([
            'patientSignature' => ['required', ...$sizeCap, $this->signatureRule()],
            'doctorSignature' => ['required', ...$sizeCap, $this->signatureRule()],
            'witness1Name' => ['nullable', 'string', 'max:255', 'required_with:witness1Signature'],
            'witness1Signature' => ['nullable', ...$sizeCap, $this->signatureRule(), 'required_with:witness1Name'],
            'witness2Name' => ['nullable', 'string', 'max:255', 'required_with:witness2Signature'],
            'witness2Signature' => ['nullable', ...$sizeCap, $this->signatureRule(), 'required_with:witness2Name'],
        ], attributes: [
            'patientSignature' => 'firma del paciente',
            'doctorSignature' => 'firma del médico',
            'witness1Name' => 'nombre del primer testigo',
            'witness1Signature' => 'firma del primer testigo',
            'witness2Name' => 'nombre del segundo testigo',
            'witness2Signature' => 'firma del segundo testigo',
        ]);

        $dir = "consents/{$consent->getKey()}/signatures";

        // Se persisten a disco las cuatro firmas ANTES de tocar la BD, registrando cada ruta
        // escrita; si algo falla a mitad se borran para no dejar PNG huérfanos (minimización, §5).
        $written = [];

        try {
            $paths = [
                'patient_signature_path' => SignatureImage::store($this->patientSignature, $dir, 'patientSignature'),
                'doctor_signature_path' => SignatureImage::store($this->doctorSignature, $dir, 'doctorSignature'),
            ];
            $written = array_values($paths);

            if (filled($this->witness1Signature)) {
                $paths['witness1_signature_path'] = $written[] = SignatureImage::store($this->witness1Signature, $dir, 'witness1Signature');
                $consent->witness1_name = $this->witness1Name;
            }
            if (filled($this->witness2Signature)) {
                $paths['witness2_signature_path'] = $written[] = SignatureImage::store($this->witness2Signature, $dir, 'witness2Signature');
                $consent->witness2_name = $this->witness2Name;
            }

            DB::transaction(function () use ($consent, $paths) {
                $consent->forceFill([...$paths, 'signed_at' => now()])->saveQuietly();
                // Evento `signed` explícito, no un `updated` sobre un documento que pasa a inmutable.
                $consent->recordSigned();
            });
        } catch (\Throwable $e) {
            foreach ($written as $path) {
                SignatureImage::delete($path);
            }

            throw $e;
        }

        session()->flash('status', 'Consentimiento firmado.');

        $this->redirectRoute('consents.show', $consent, navigate: true);
    }

    private function signatureRule(): callable
    {
        return function (string $attribute, mixed $value, callable $fail) {
            if (! SignatureImage::looksLikePngDataUrl(is_string($value) ? $value : null)) {
                $fail('Falta la firma en el recuadro.');
            }
        };
    }

    public function render(): View
    {
        return view('livewire.consents.sign-consent', [
            'consent' => Consent::with('patient', 'doctor:id,name')->findOrFail($this->consentId),
        ]);
    }
}
