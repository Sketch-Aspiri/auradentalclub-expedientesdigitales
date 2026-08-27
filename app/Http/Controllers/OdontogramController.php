<?php

namespace App\Http\Controllers;

use App\Models\OdontogramRecord;
use App\Models\Patient;
use Illuminate\View\View;

/**
 * Odontograma. La interacción (seleccionar diente, registrar hallazgos, ver historial)
 * vive en el componente Livewire App\Livewire\Patients\Odontogram; estos controladores
 * solo resuelven el acceso y auditan la apertura del expediente.
 *
 * - `index`  → pantalla global: buscar un paciente y ver su odontograma (Livewire\Odontogram\Browser).
 * - `show`   → odontograma de un paciente concreto, enlazado desde su ficha.
 */
class OdontogramController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', OdontogramRecord::class);

        return view('odontogram.index');
    }

    public function show(Patient $patient): View
    {
        $this->authorize('viewAny', OdontogramRecord::class);

        // El odontograma expone el estado clínico de cada pieza: abrirlo es acceso al
        // expediente y se audita como tal (CLAUDE.md §5, NOM-004).
        $patient->recordView();

        return view('patients.odontogram.show', ['patient' => $patient]);
    }
}
