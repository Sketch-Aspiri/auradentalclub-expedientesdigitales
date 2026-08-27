<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\StoreConsultationRequest;
use App\Http\Requests\UpdateConsultationRequest;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ConsultationController extends Controller
{
    public function index(Patient $patient): View
    {
        $this->authorize('viewAny', Consultation::class);

        // El listado expone los diagnósticos (PHI cifrado) de todo el historial del paciente,
        // así que acceder a él se audita como acceso al expediente clínico (CLAUDE.md §5, NOM-004).
        $patient->recordView();

        $consultations = $patient->consultations()
            ->with('doctor:id,name')
            ->orderByDesc('consultation_date')
            ->orderByDesc('id')
            ->paginate(15);

        return view('patients.consultations.index', [
            'patient' => $patient,
            'consultations' => $consultations,
        ]);
    }

    public function create(Patient $patient): View
    {
        $this->authorize('create', Consultation::class);

        return view('patients.consultations.create', [
            'patient' => $patient,
            'consultation' => new Consultation,
            'doctors' => $this->doctorsForSelector(),
        ]);
    }

    public function store(StoreConsultationRequest $request, Patient $patient): RedirectResponse
    {
        $consultation = $patient->consultations()->create($request->validated());

        return redirect()->route('consultations.show', $consultation)
            ->with('status', 'Consulta registrada correctamente.');
    }

    public function show(Consultation $consultation): View
    {
        $this->authorize('view', $consultation);

        $consultation->load('doctor:id,name', 'patient');
        $consultation->recordView();

        return view('patients.consultations.show', [
            'consultation' => $consultation,
            'patient' => $consultation->patient,
        ]);
    }

    public function edit(Consultation $consultation): View
    {
        $this->authorize('update', $consultation);

        return view('patients.consultations.edit', [
            'patient' => $consultation->patient,
            'consultation' => $consultation,
            'doctors' => $this->doctorsForSelector(),
        ]);
    }

    public function update(UpdateConsultationRequest $request, Consultation $consultation): RedirectResponse
    {
        $consultation->update($request->validated());

        return redirect()->route('consultations.show', $consultation)
            ->with('status', 'Consulta actualizada correctamente.');
    }

    public function destroy(Consultation $consultation): RedirectResponse
    {
        $this->authorize('delete', $consultation);

        $patient = $consultation->patient;
        $consultation->delete();

        return redirect()->route('patients.consultations.index', $patient)
            ->with('status', 'Consulta eliminada correctamente.');
    }

    /**
     * Lista de doctores para el selector de "doctor tratante". Vacía si el usuario actual
     * es doctor (en ese caso la consulta se le asigna automáticamente, ver los Form Requests).
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
