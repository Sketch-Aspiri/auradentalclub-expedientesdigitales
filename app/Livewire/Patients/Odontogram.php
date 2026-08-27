<?php

namespace App\Livewire\Patients;

use App\Enums\ToothStatus;
use App\Enums\ToothSurface;
use App\Models\OdontogramRecord;
use App\Models\Patient;
use App\Support\Dentition;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Odontograma interactivo por superficie con historial (Sprint 4).
 *
 * El estado "vigente" de cada superficie / diente completo es el hallazgo más reciente
 * no archivado; registrar un hallazgo nunca sobrescribe, solo anexa una fila. La
 * persistencia y la auditoría (evento `created` del trait Auditable) viven en el modelo.
 */
class Odontogram extends Component
{
    #[Locked]
    public int $patientId;

    #[Locked]
    public ?int $selectedTooth = null;

    /** Valor de ToothSurface, o null para un hallazgo sobre el diente completo. */
    #[Locked]
    public ?string $selectedSurface = null;

    public string $status = '';

    public string $note = '';

    public string $recordedAt = '';

    /** Muestra los hallazgos archivados (soft-deleted) de la pieza seleccionada. */
    public bool $showArchived = false;

    public function mount(Patient $patient): void
    {
        $this->authorize('viewAny', OdontogramRecord::class);

        $this->patientId = $patient->getKey();
        $this->recordedAt = now()->toDateString();
    }

    #[Computed]
    public function patient(): Patient
    {
        return Patient::findOrFail($this->patientId);
    }

    /**
     * Estado vigente por diente: ['whole' => ToothStatus, 'mesial' => ToothStatus, ...].
     *
     * @return array<int, array<string, ToothStatus>>
     */
    #[Computed]
    public function currentState(): array
    {
        $this->authorize('viewAny', OdontogramRecord::class);

        $state = [];

        $records = OdontogramRecord::query()
            ->where('patient_id', $this->patientId)
            ->orderedForHistory()
            ->get(['id', 'tooth_number', 'surface', 'status']);

        foreach ($records as $record) {
            $key = $record->surface?->value ?? 'whole';

            $state[$record->tooth_number] ??= [];
            // orderedForHistory() ya ordena del más reciente al más antiguo:
            // la primera aparición de cada clave es el estado vigente.
            $state[$record->tooth_number][$key] ??= $record->status;
        }

        return $state;
    }

    /**
     * @return Collection<int, OdontogramRecord>
     */
    #[Computed]
    public function selectedToothHistory(): Collection
    {
        $this->authorize('viewAny', OdontogramRecord::class);

        if ($this->selectedTooth === null) {
            return collect();
        }

        return OdontogramRecord::query()
            ->where('patient_id', $this->patientId)
            ->where('tooth_number', $this->selectedTooth)
            ->with('recordedBy:id,name')
            ->orderedForHistory()
            ->get();
    }

    /**
     * Hallazgos archivados (soft-deleted) de la pieza seleccionada, para restaurarlos.
     *
     * @return Collection<int, OdontogramRecord>
     */
    #[Computed]
    public function archivedToothHistory(): Collection
    {
        $this->authorize('viewAny', OdontogramRecord::class);

        if ($this->selectedTooth === null) {
            return collect();
        }

        return OdontogramRecord::onlyTrashed()
            ->where('patient_id', $this->patientId)
            ->where('tooth_number', $this->selectedTooth)
            ->with('recordedBy:id,name')
            ->orderedForHistory()
            ->get();
    }

    public function select(int $tooth, ?string $surface = null): void
    {
        $this->authorize('viewAny', OdontogramRecord::class);

        abort_unless(Dentition::isValid($tooth), 404);

        if ($surface !== null && ToothSurface::tryFrom($surface) === null) {
            $surface = null;
        }

        $this->selectedTooth = $tooth;
        $this->selectedSurface = $surface;
        $this->showArchived = false;
        $this->resetFindingForm();

        unset($this->selectedToothHistory, $this->archivedToothHistory);
    }

    public function clearSelection(): void
    {
        $this->selectedTooth = null;
        $this->selectedSurface = null;
        $this->showArchived = false;
        $this->resetFindingForm();
        unset($this->selectedToothHistory, $this->archivedToothHistory);
    }

    public function toggleArchived(): void
    {
        $this->showArchived = ! $this->showArchived;
    }

    public function save(): void
    {
        $this->authorize('create', OdontogramRecord::class);

        abort_unless(
            $this->selectedTooth !== null && Dentition::isValid($this->selectedTooth),
            404,
        );

        $validated = $this->validate([
            'status' => ['required', Rule::enum(ToothStatus::class)],
            'note' => ['nullable', 'string', 'max:2000'],
            'recordedAt' => ['required', 'date', 'before_or_equal:today'],
        ], attributes: [
            'status' => 'estado',
            'note' => 'nota',
            'recordedAt' => 'fecha',
        ]);

        $status = ToothStatus::from($validated['status']);
        $onSurface = $this->selectedSurface !== null;

        if ($onSurface && ! $status->appliesToSurface()) {
            $this->addError('status', 'Ese estado no se registra por superficie; elige «Diente completo».');

            return;
        }

        if (! $onSurface && ! $status->appliesToWholeTooth()) {
            $this->addError('status', 'Ese estado se registra sobre una superficie concreta, no en el diente completo.');

            return;
        }

        $this->patient->odontogramRecords()->create([
            'recorded_by' => auth()->id(),
            'tooth_number' => $this->selectedTooth,
            'surface' => $this->selectedSurface,
            'status' => $status,
            'note' => filled($this->note) ? $this->note : null,
            'recorded_at' => $validated['recordedAt'],
        ]);

        unset($this->currentState, $this->selectedToothHistory);
        $this->resetFindingForm();

        session()->flash('odontogram-status', "Hallazgo registrado en el diente {$this->selectedTooth}.");
    }

    public function deleteRecord(int $recordId): void
    {
        $record = OdontogramRecord::query()
            ->where('patient_id', $this->patientId)
            ->findOrFail($recordId);

        $this->authorize('delete', $record);

        $record->delete();

        unset($this->currentState, $this->selectedToothHistory, $this->archivedToothHistory);

        session()->flash('odontogram-status', 'Hallazgo archivado del historial de la pieza.');
    }

    public function restoreRecord(int $recordId): void
    {
        $record = OdontogramRecord::onlyTrashed()
            ->where('patient_id', $this->patientId)
            ->findOrFail($recordId);

        $this->authorize('restore', $record);

        $record->restore();

        unset($this->currentState, $this->selectedToothHistory, $this->archivedToothHistory);

        session()->flash('odontogram-status', 'Hallazgo restaurado al historial de la pieza.');
    }

    private function resetFindingForm(): void
    {
        $this->status = '';
        $this->note = '';
        $this->recordedAt = now()->toDateString();
        $this->resetValidation();
    }

    public function render(): View
    {
        return view('livewire.patients.odontogram', [
            'arches' => [
                'Arcada superior' => [Dentition::UPPER_RIGHT, Dentition::UPPER_LEFT],
                'Arcada inferior' => [Dentition::LOWER_RIGHT, Dentition::LOWER_LEFT],
            ],
            'statusCatalog' => ToothStatus::catalog(),
        ]);
    }
}
