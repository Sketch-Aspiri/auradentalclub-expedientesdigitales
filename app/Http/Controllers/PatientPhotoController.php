<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Support\PatientPhoto;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Sirve la foto de identificación del paciente desde el disco privado `local`. Nunca hay
 * URL directa al archivo (CLAUDE.md §5): esta ruta exige sesión + policy `view` del paciente.
 *
 * Auditoría: se registra un evento `viewed` a lo sumo una vez por usuario / paciente / día
 * (recordViewOncePerDay). La foto se pide en cada fila de un listado, así que un `viewed`
 * por request inundaría `audit_logs`; el dedup conserva el rastro de "quién accedió al
 * expediente hoy" —incluido el acceso directo a esta URL, no solo la navegación por la UI—
 * sin volumen. Las mutaciones de la foto (subir/reemplazar/quitar) ya quedan auditadas por
 * el evento `updated` del propio paciente.
 */
class PatientPhotoController extends Controller
{
    public function __invoke(Patient $patient): BinaryFileResponse
    {
        $this->authorize('view', $patient);

        abort_unless(PatientPhoto::exists($patient->photo_path), 404);

        $patient->recordViewOncePerDay();

        return response()->file(
            Storage::disk(PatientPhoto::DISK)->path($patient->photo_path),
            [
                // Foto de la cara de un paciente en estaciones de trabajo compartidas de la
                // clínica: no debe quedar en la caché del navegador tras cerrar sesión.
                'Cache-Control' => 'private, no-store',
                'X-Content-Type-Options' => 'nosniff',
                'Content-Disposition' => 'inline',
            ],
        );
    }
}
