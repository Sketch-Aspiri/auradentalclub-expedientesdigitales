<?php

namespace App\Livewire\Patients;

use App\Models\Patient;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Listado de pacientes con búsqueda en vivo (nombre / teléfono), alternancia entre
 * activos y archivados, y restauración sin recargar. La autorización de cada acción se
 * verifica explícitamente; la ruta `patients.index` sigue existiendo vía PatientController,
 * que solo renderiza el contenedor de este componente.
 */
class PatientList extends Component
{
    use WithPagination;

    // Sin `history: true`: la búsqueda en vivo hace pushState en cada pausa de tecleo y
    // dejaría fragmentos del nombre del paciente en el historial del navegador de una
    // estación compartida (CLAUDE.md §5). Con replaceState el término sigue en la barra
    // de direcciones y sobrevive a un refresh, pero no satura el botón «atrás».
    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public bool $archived = false;

    private const PER_PAGE = 15;

    public function mount(): void
    {
        $this->authorize('viewAny', Patient::class);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function toggleArchived(): void
    {
        $this->archived = ! $this->archived;
        $this->resetPage();
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    public function restore(int $patientId): void
    {
        $patient = Patient::onlyTrashed()->findOrFail($patientId);

        $this->authorize('restore', $patient);

        $patient->restore();

        session()->flash('status', 'Paciente restaurado correctamente.');
    }

    /**
     * @return LengthAwarePaginator<int, Patient>
     */
    private function patients(): LengthAwarePaginator
    {
        $this->authorize('viewAny', Patient::class);

        $search = trim($this->search);

        return Patient::query()
            ->when($this->archived, fn ($query) => $query->onlyTrashed())
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('full_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->orderBy('full_name')
            ->paginate(self::PER_PAGE);
    }

    public function render(): View
    {
        return view('livewire.patients.patient-list', [
            'patients' => $this->patients(),
        ]);
    }
}
