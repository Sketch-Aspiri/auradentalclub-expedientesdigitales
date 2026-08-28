<?php

namespace App\Http\Controllers;

use App\Enums\ConsentGiver;
use App\Enums\ConsentType;
use App\Enums\UserRole;
use App\Http\Requests\StoreConsentRequest;
use App\Http\Requests\UpdateConsentRequest;
use App\Http\Requests\VoidConsentRequest;
use App\Models\Consent;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ConsentController extends Controller
{
    /**
     * Pantalla global: busca un paciente y salta a sus consentimientos. Punto de entrada desde
     * el dashboard y la navegación; el acceso por ficha del paciente sigue disponible.
     */
    public function browse(): View
    {
        $this->authorize('viewAny', Consent::class);

        return view('consents.browse');
    }

    public function index(Patient $patient): View
    {
        $this->authorize('viewAny', Consent::class);

        // El listado expone datos clínicos de todo el historial de consentimientos, así que
        // acceder a él se audita como acceso al expediente (CLAUDE.md §5, NOM-004).
        $patient->recordView();

        $consents = $patient->consents()
            ->with('doctor:id,name')
            ->orderedForHistory()
            ->paginate(15);

        $archivedConsents = $patient->consents()
            ->onlyTrashed()
            ->with('doctor:id,name')
            ->orderedForHistory()
            ->limit(100)
            ->get();

        return view('patients.consents.index', [
            'patient' => $patient,
            'consents' => $consents,
            'archivedConsents' => $archivedConsents,
        ]);
    }

    public function create(Patient $patient): View
    {
        $this->authorize('create', Consent::class);

        // El formulario muestra alergias y medicación del paciente (PHI descifrada) para la
        // copia fija; abrirlo se audita como acceso al expediente (CLAUDE.md §5, NOM-004).
        $patient->recordView();

        return view('patients.consents.create', [
            'patient' => $patient->load('medicalHistory'),
            'consent' => new Consent,
            'doctors' => $this->doctorsForSelector(),
            'types' => ConsentType::catalog(),
            'givers' => ConsentGiver::catalog(),
        ]);
    }

    public function store(StoreConsentRequest $request, Patient $patient): RedirectResponse
    {
        $consent = $patient->consents()->make($request->validated());

        // Copia fija de las respuestas de salud del paciente al momento de crear (sección
        // "Diagnóstico" de la hoja). Se asigna antes del insert para que el consentimiento
        // nazca con un único evento `created` en audit_logs, sin un `updated` espurio.
        $consent->health_snapshot = Consent::snapshotHealthFrom($patient->medicalHistory);
        $consent->save();

        return redirect()->route('consents.show', $consent)
            ->with('status', 'Consentimiento creado como borrador.');
    }

    public function show(Consent $consent): View
    {
        $this->authorize('view', $consent);

        $consent->load('doctor:id,name', 'patient', 'voidedBy:id,name');
        $consent->recordView();

        return view('consents.show', [
            'consent' => $consent,
            'patient' => $consent->patient,
        ]);
    }

    public function edit(Consent $consent): View
    {
        $this->authorize('update', $consent);

        // El formulario muestra PHI del paciente (alergias, medicación); auditar el acceso.
        $consent->patient->recordView();

        return view('patients.consents.edit', [
            'patient' => $consent->patient->load('medicalHistory'),
            'consent' => $consent,
            'doctors' => $this->doctorsForSelector(),
            'types' => ConsentType::catalog(),
            'givers' => ConsentGiver::catalog(),
        ]);
    }

    public function update(UpdateConsentRequest $request, Consent $consent): RedirectResponse
    {
        $consent->update($request->validated());

        return redirect()->route('consents.show', $consent)
            ->with('status', 'Consentimiento actualizado.');
    }

    public function destroy(Consent $consent): RedirectResponse
    {
        $this->authorize('delete', $consent);

        $patient = $consent->patient;
        $consent->delete();

        return redirect()->route('patients.consents.index', $patient)
            ->with('status', 'Consentimiento archivado.');
    }

    public function restore(Consent $consent): RedirectResponse
    {
        $this->authorize('restore', $consent);

        $consent->restore();

        return redirect()->route('patients.consents.index', $consent->patient)
            ->with('status', 'Consentimiento restaurado.');
    }

    public function sign(Consent $consent): View
    {
        $this->authorize('sign', $consent);

        $consent->load('patient', 'doctor:id,name');

        return view('consents.sign', [
            'consent' => $consent,
            'patient' => $consent->patient,
        ]);
    }

    public function void(VoidConsentRequest $request, Consent $consent): RedirectResponse
    {
        // La autorización (consentimiento firmado + rol) la resuelve VoidConsentRequest::authorize().
        // saveQuietly: la transición a "anulado" se audita como evento `voided`, no como un
        // `updated` genérico sobre un documento que ya es inmutable.
        $consent->forceFill([
            'voided_at' => now(),
            'voided_by' => $request->user()->id,
            'void_reason' => $request->validated('void_reason'),
        ])->saveQuietly();

        $consent->recordVoided();

        return redirect()->route('consents.show', $consent)
            ->with('status', 'Consentimiento anulado.');
    }

    public function print(Consent $consent): View
    {
        $this->authorize('view', $consent);

        $consent->load('doctor:id,name', 'patient.medicalHistory', 'voidedBy:id,name');
        $consent->recordView();

        return view('consents.print', [
            'consent' => $consent,
            'patient' => $consent->patient,
        ]);
    }

    /**
     * Lista de doctores para el selector de "doctor responsable". Vacía si el usuario actual es
     * doctor (en ese caso el consentimiento se le asigna automáticamente, ver los Form Requests).
     *
     * @return Collection<int, User>
     */
    private function doctorsForSelector(): Collection
    {
        if (request()->user()->isDoctor()) {
            return collect();
        }

        return User::query()
            ->where('role', UserRole::Doctor)
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
