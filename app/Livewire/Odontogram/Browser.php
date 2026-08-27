<?php

namespace App\Livewire\Odontogram;

use App\Models\OdontogramRecord;
use App\Models\Patient;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Pantalla global del odontograma: busca un paciente y muestra su odontograma interactivo
 * embebido (componente App\Livewire\Patients\Odontogram). El acceso por paciente sigue
 * disponible desde su ficha; esta pantalla es el punto de entrada desde el dashboard / nav.
 */
class Browser extends Component
{
    /**
     * `#[Locked]`: no se puede fijar desde el cliente. Cambiar de paciente pasa siempre por
     * `selectPatient()` / `mount()`, que re-autorizan (`view`) y auditan (`recordView`).
     */
    #[Locked]
    #[Url(as: 'paciente')]
    public ?int $patientId = null;

    public string $search = '';

    private const MAX_RESULTS = 8;

    public function mount(): void
    {
        $this->authorize('viewAny', OdontogramRecord::class);

        // `?paciente=` puede venir manipulado o apuntar a un id inexistente.
        $patient = $this->patientId !== null ? Patient::find($this->patientId) : null;

        if ($patient === null) {
            $this->patientId = null;

            return;
        }

        $this->authorize('view', $patient);
        $patient->recordView();
    }

    #[Computed]
    public function selectedPatient(): ?Patient
    {
        $this->authorize('viewAny', OdontogramRecord::class);

        return $this->patientId !== null ? Patient::find($this->patientId) : null;
    }

    /**
     * @return Collection<int, Patient>
     */
    #[Computed]
    public function results(): Collection
    {
        $this->authorize('viewAny', OdontogramRecord::class);

        $search = trim($this->search);

        if ($search === '') {
            return collect();
        }

        return Patient::query()
            ->where(function ($query) use ($search) {
                $query->where('full_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            })
            ->orderBy('full_name')
            ->limit(self::MAX_RESULTS)
            ->get(['id', 'full_name', 'phone', 'birth_date', 'photo_path']);
    }

    public function selectPatient(int $patientId): void
    {
        $patient = Patient::findOrFail($patientId);

        $this->authorize('view', $patient);

        $patient->recordView();

        $this->patientId = $patient->getKey();
        $this->search = '';
    }

    public function clearPatient(): void
    {
        $this->patientId = null;
        $this->search = '';
    }

    public function render(): View
    {
        return view('livewire.odontogram.browser');
    }
}
