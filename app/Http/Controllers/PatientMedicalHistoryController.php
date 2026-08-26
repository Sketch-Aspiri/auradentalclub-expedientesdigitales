<?php

namespace App\Http\Controllers;

use App\Http\Requests\MedicalHistoryRequest;
use App\Models\MedicalHistory;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PatientMedicalHistoryController extends Controller
{
    public function edit(Patient $patient): View
    {
        $medicalHistory = $patient->medicalHistory ?? new MedicalHistory(['patient_id' => $patient->id]);

        $this->authorize($medicalHistory->exists ? 'view' : 'create', $medicalHistory->exists ? $medicalHistory : MedicalHistory::class);

        if ($medicalHistory->exists) {
            $medicalHistory->recordView();
        }

        return view('patients.medical-history.edit', [
            'patient' => $patient,
            'medicalHistory' => $medicalHistory,
        ]);
    }

    public function update(MedicalHistoryRequest $request, Patient $patient): RedirectResponse
    {
        $patient->medicalHistory()->updateOrCreate(
            ['patient_id' => $patient->id],
            $request->validated(),
        );

        return redirect()->route('patients.show', $patient)
            ->with('status', 'Historia clínica guardada correctamente.');
    }
}
