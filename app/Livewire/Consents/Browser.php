<?php

namespace App\Livewire\Consents;

use App\Models\Consent;
use App\Models\Patient;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Pantalla global de consentimientos: busca un paciente y salta a su listado de consentimientos.
 * Punto de entrada desde el dashboard y la navegación; el acceso por ficha del paciente sigue
 * disponible.
 */
class Browser extends Component
{
    public string $search = '';

    private const MAX_RESULTS = 8;

    public function mount(): void
    {
        $this->authorize('viewAny', Consent::class);
    }

    /**
     * @return Collection<int, Patient>
     */
    #[Computed]
    public function results(): Collection
    {
        $this->authorize('viewAny', Consent::class);

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

        $this->redirectRoute('patients.consents.index', $patient, navigate: true);
    }

    public function render(): View
    {
        return view('livewire.consents.browser');
    }
}
