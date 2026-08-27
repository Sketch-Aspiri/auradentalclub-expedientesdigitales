<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Patient::class);

        $search = trim((string) $request->query('q', ''));
        $showArchived = $request->boolean('archived');

        $patients = Patient::query()
            ->when($showArchived, fn ($query) => $query->onlyTrashed())
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('full_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->orderBy('full_name')
            ->paginate(15)
            ->withQueryString();

        return view('patients.index', [
            'patients' => $patients,
            'search' => $search,
            'showArchived' => $showArchived,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Patient::class);

        return view('patients.create');
    }

    public function store(StorePatientRequest $request): RedirectResponse
    {
        $patient = Patient::create($request->validated());

        return redirect()->route('patients.show', $patient)
            ->with('status', 'Paciente creado correctamente.');
    }

    public function show(Patient $patient): View
    {
        $this->authorize('view', $patient);

        $patient->recordView();

        return view('patients.show', ['patient' => $patient]);
    }

    public function edit(Patient $patient): View
    {
        $this->authorize('update', $patient);

        return view('patients.edit', ['patient' => $patient]);
    }

    public function update(UpdatePatientRequest $request, Patient $patient): RedirectResponse
    {
        $patient->update($request->validated());

        return redirect()->route('patients.show', $patient)
            ->with('status', 'Paciente actualizado correctamente.');
    }

    public function destroy(Patient $patient): RedirectResponse
    {
        $this->authorize('delete', $patient);

        $patient->delete();

        return redirect()->route('patients.index')
            ->with('status', 'Paciente eliminado correctamente.');
    }

    public function restore(Patient $patient): RedirectResponse
    {
        $this->authorize('restore', $patient);

        $patient->restore();

        return redirect()->route('patients.show', $patient)
            ->with('status', 'Paciente restaurado correctamente.');
    }
}
