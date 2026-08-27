<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Models\Patient;
use App\Support\PatientPhoto;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PatientController extends Controller
{
    /**
     * La búsqueda, la paginación y el listado de archivados viven en el componente Livewire
     * App\Livewire\Patients\PatientList; aquí solo se resuelve el acceso y se renderiza el
     * contenedor, para conservar la ruta `patients.index`.
     */
    public function index(): View
    {
        $this->authorize('viewAny', Patient::class);

        return view('patients.index');
    }

    public function create(): View
    {
        $this->authorize('create', Patient::class);

        return view('patients.create');
    }

    public function store(StorePatientRequest $request): RedirectResponse
    {
        $patient = new Patient($request->safe()->except(['photo', 'remove_photo']));

        if ($photo = $request->file('photo')) {
            $patient->photo_path = PatientPhoto::store($photo);
        }

        $patient->save();

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
        $previousPhoto = $patient->photo_path;

        $patient->fill($request->safe()->except(['photo', 'remove_photo']));

        if ($photo = $request->file('photo')) {
            $patient->photo_path = PatientPhoto::store($photo);
        } elseif ($request->boolean('remove_photo')) {
            $patient->photo_path = null;
        }

        $patient->save();

        // La foto anterior se borra solo tras un guardado exitoso y solo si cambió.
        if ($previousPhoto !== null && $previousPhoto !== $patient->photo_path) {
            PatientPhoto::delete($previousPhoto);
        }

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
