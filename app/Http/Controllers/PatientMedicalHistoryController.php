<?php

namespace App\Http\Controllers;

use App\Http\Requests\MedicalHistoryRequest;
use App\Models\MedicalHistory;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PatientMedicalHistoryController extends Controller
{
    /**
     * Pantalla de consulta (solo lectura). Aquí se registra el `recordView()` — es la
     * pantalla pensada para "ver" el expediente y donde corresponde auditar el acceso.
     */
    public function show(Patient $patient): View
    {
        $medicalHistory = $this->authorizeAndRecordView($patient);

        return view('patients.medical-history.show', [
            'patient' => $patient,
            'medicalHistory' => $medicalHistory,
        ]);
    }

    /**
     * Pantalla de edición. También muestra los datos actuales en el formulario, así que
     * sigue siendo una visualización auditable — se mantiene el `recordView()` aquí también.
     */
    public function edit(Patient $patient): View
    {
        $medicalHistory = $this->authorizeAndRecordView($patient);

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

        return redirect()->route('patients.medical-history.show', $patient)
            ->with('status', 'Historia clínica guardada correctamente.');
    }

    /**
     * Resuelve la historia clínica del paciente (o una instancia vacía si aún no existe),
     * autoriza el acceso y registra la vista en `audit_logs`. Los tres pasos viven juntos
     * a propósito: es la ruta sensible de acceso a PHI y un cambio futuro debe tocar un
     * solo lugar, no repetirse en cada pantalla que muestre la historia.
     *
     * No hay nada que auditar si la historia todavía no se ha capturado.
     */
    private function authorizeAndRecordView(Patient $patient): MedicalHistory
    {
        $medicalHistory = $patient->medicalHistory ?? new MedicalHistory(['patient_id' => $patient->id]);

        $this->authorize($medicalHistory->exists ? 'view' : 'create', $medicalHistory->exists ? $medicalHistory : MedicalHistory::class);

        if ($medicalHistory->exists) {
            $medicalHistory->recordView();
        }

        return $medicalHistory;
    }
}
