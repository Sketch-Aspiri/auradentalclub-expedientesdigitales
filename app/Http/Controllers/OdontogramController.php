<?php

namespace App\Http\Controllers;

use App\Models\OdontogramRecord;
use App\Models\Patient;
use Illuminate\View\View;

/**
 * Pantalla del odontograma de un paciente. La interacción (seleccionar diente, registrar
 * hallazgos, ver historial) vive en el componente Livewire App\Livewire\Patients\Odontogram;
 * este controlador solo resuelve el acceso y audita la apertura del expediente.
 */
class OdontogramController extends Controller
{
    public function __invoke(Patient $patient): View
    {
        $this->authorize('viewAny', OdontogramRecord::class);

        // El odontograma expone el estado clínico de cada pieza: abrirlo es acceso al
        // expediente y se audita como tal (CLAUDE.md §5, NOM-004).
        $patient->recordView();

        return view('patients.odontogram.show', ['patient' => $patient]);
    }
}
