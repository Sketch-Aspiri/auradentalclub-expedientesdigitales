<?php

namespace App\Http\Controllers;

use App\Models\Consent;
use App\Support\SignatureImage;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Sirve una firma de un consentimiento desde el disco privado `local`. Nunca hay URL directa al
 * archivo (CLAUDE.md §5): esta ruta exige sesión + policy `view` del consentimiento.
 *
 * Auditoría: se registra un evento `viewed` sobre el paciente a lo sumo una vez por
 * usuario/paciente/día (recordViewOncePerDay) — la firma se pide desde la vista de detalle y la
 * de impresión, así que un `viewed` por request inundaría audit_logs; el dedup conserva el
 * rastro de "quién accedió al expediente hoy", incluido el acceso directo a esta URL.
 */
class ConsentSignatureController extends Controller
{
    private const PARTY_COLUMNS = [
        'patient' => 'patient_signature_path',
        'doctor' => 'doctor_signature_path',
        'witness1' => 'witness1_signature_path',
        'witness2' => 'witness2_signature_path',
    ];

    public function __invoke(Consent $consent, string $party): BinaryFileResponse
    {
        $this->authorize('view', $consent);

        $column = self::PARTY_COLUMNS[$party] ?? abort(404);
        $path = $consent->{$column};

        abort_unless(SignatureImage::exists($path), 404);

        $consent->patient->recordViewOncePerDay();

        return response()->file(
            Storage::disk(SignatureImage::DISK)->path($path),
            [
                'Cache-Control' => 'private, no-store',
                'X-Content-Type-Options' => 'nosniff',
                'Content-Disposition' => 'inline',
            ],
        );
    }
}
