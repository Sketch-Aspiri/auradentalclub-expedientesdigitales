<x-app-layout :title="$patient->full_name">
    <div class="max-w-3xl">
        <div class="flex items-start justify-between mb-6 gap-4">
            <div class="flex items-start gap-4">
                <x-patient-avatar :patient="$patient" size="md" />
                <div>
                    <h1 class="text-lg font-medium">{{ $patient->full_name }}</h1>
                    <p class="text-sm text-aura-gray-dark">{{ $patient->age }} años · {{ $patient->sex === 'M' ? 'Masculino' : 'Femenino' }}</p>
                </div>
            </div>

            <div class="flex items-center gap-3 text-sm">
                @can('viewAny', App\Models\MedicalHistory::class)
                    <a href="{{ route('patients.medical-history.edit', $patient) }}" class="text-aura-olive hover:underline">
                        Historia clínica
                    </a>
                @endcan

                @can('viewAny', App\Models\Consultation::class)
                    <a href="{{ route('patients.consultations.index', $patient) }}" class="text-aura-olive hover:underline">
                        Consultas
                    </a>
                @endcan

                @can('viewAny', App\Models\OdontogramRecord::class)
                    <a href="{{ route('patients.odontogram', $patient) }}" class="text-aura-olive hover:underline">
                        Odontograma
                    </a>
                @endcan

                @can('update', $patient)
                    <a href="{{ route('patients.edit', $patient) }}" class="text-aura-olive hover:underline">
                        Editar
                    </a>
                @endcan

                @can('delete', $patient)
                    <form method="POST" action="{{ route('patients.destroy', $patient) }}"
                          onsubmit="return confirm('¿Eliminar este paciente? Quedará archivado y se podrá restaurar desde «Ver archivados».');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                    </form>
                @endcan
            </div>
        </div>

        @if (session('status'))
            <p class="mb-4 text-sm text-aura-olive">{{ session('status') }}</p>
        @endif

        <div class="bg-white border border-aura-gray-light rounded-lg p-6 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-aura-gray text-xs uppercase tracking-wide mb-1">Fecha de nacimiento</p>
                <p>{{ $patient->birth_date->format('d/m/Y') }}</p>
            </div>
            <div>
                <p class="text-aura-gray text-xs uppercase tracking-wide mb-1">Ocupación</p>
                <p>{{ $patient->occupation ?? '—' }}</p>
            </div>
            <div>
                <p class="text-aura-gray text-xs uppercase tracking-wide mb-1">Estado civil</p>
                <p>{{ $patient->marital_status ?? '—' }}</p>
            </div>
            <div>
                <p class="text-aura-gray text-xs uppercase tracking-wide mb-1">Teléfono</p>
                <p>{{ $patient->phone }}</p>
            </div>
            <div class="sm:col-span-2">
                <p class="text-aura-gray text-xs uppercase tracking-wide mb-1">Dirección</p>
                <p>{{ $patient->address ?? '—' }}</p>
            </div>
            <div>
                <p class="text-aura-gray text-xs uppercase tracking-wide mb-1">Correo electrónico</p>
                <p>{{ $patient->email ?? '—' }}</p>
            </div>
            <div>
                <p class="text-aura-gray text-xs uppercase tracking-wide mb-1">Contacto de emergencia</p>
                <p>{{ $patient->emergency_contact_name ?? '—' }} @if($patient->emergency_contact_phone) · {{ $patient->emergency_contact_phone }} @endif</p>
            </div>
        </div>

        <a href="{{ route('patients.index') }}" class="inline-block mt-6 text-sm text-aura-gray hover:text-aura-gray-dark">
            &larr; Volver al listado
        </a>
    </div>
</x-app-layout>
