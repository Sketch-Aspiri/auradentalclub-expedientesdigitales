<x-app-layout title="Pacientes">
    <div class="flex items-start justify-between mb-6 gap-4">
        <div>
            <h1 class="text-lg font-medium">{{ $showArchived ? 'Pacientes archivados' : 'Pacientes' }}</h1>
            @if ($showArchived)
                <p class="text-sm text-aura-gray-dark mt-1">
                    Expedientes archivados. Restaurar uno lo devuelve a la lista de pacientes activos.
                </p>
            @endif
        </div>

        @can('create', App\Models\Patient::class)
            @unless ($showArchived)
                <a href="{{ route('patients.create') }}"
                   class="bg-aura-olive text-white rounded px-4 py-2 text-sm font-medium hover:opacity-90">
                    Nuevo paciente
                </a>
            @endunless
        @endcan
    </div>

    @if (session('status'))
        <p class="mb-4 text-sm text-aura-olive">{{ session('status') }}</p>
    @endif

    <div class="flex items-center justify-between mb-6 gap-4">
        <form method="GET" action="{{ route('patients.index') }}" class="max-w-sm flex-1">
            @if ($showArchived)
                <input type="hidden" name="archived" value="1">
            @endif
            <input type="text" name="q" value="{{ $search }}" placeholder="Buscar por nombre o teléfono..."
                   class="w-full rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">
        </form>

        <a href="{{ route('patients.index', $showArchived ? [] : ['archived' => 1]) }}"
           class="text-sm text-aura-gray-dark hover:text-aura-olive whitespace-nowrap rounded px-1 py-1 -mr-1 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-aura-olive">
            {{ $showArchived ? '← Ver pacientes activos' : 'Ver archivados' }}
        </a>
    </div>

    <div class="bg-white border border-aura-gray-light rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-aura-cream text-aura-gray text-xs uppercase tracking-wide">
                <tr>
                    <th class="text-left px-4 py-3">Nombre</th>
                    <th class="text-left px-4 py-3">Edad</th>
                    <th class="text-left px-4 py-3">Teléfono</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-aura-gray-light">
                @forelse ($patients as $patient)
                    <tr>
                        <td class="px-4 py-3">{{ $patient->full_name }}</td>
                        <td class="px-4 py-3">{{ $patient->age }}</td>
                        <td class="px-4 py-3">{{ $patient->phone }}</td>
                        <td class="px-4 py-3 text-right">
                            @if ($showArchived)
                                @can('restore', $patient)
                                    <form method="POST" action="{{ route('patients.restore', $patient) }}">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit"
                                                class="inline-flex items-center rounded px-2 py-1 -my-1 text-aura-olive hover:bg-aura-cream focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-aura-olive">
                                            Restaurar
                                        </button>
                                    </form>
                                @endcan
                            @else
                                <a href="{{ route('patients.show', $patient) }}" class="text-aura-olive hover:underline">
                                    Ver
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-aura-gray">
                            {{ $showArchived ? 'No hay pacientes archivados.' : 'No se encontraron pacientes.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $patients->links() }}
    </div>
</x-app-layout>
