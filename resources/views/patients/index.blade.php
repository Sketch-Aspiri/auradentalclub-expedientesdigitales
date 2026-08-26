<x-app-layout title="Pacientes">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-lg font-medium">Pacientes</h1>

        @can('create', App\Models\Patient::class)
            <a href="{{ route('patients.create') }}"
               class="bg-aura-olive text-white rounded px-4 py-2 text-sm font-medium hover:opacity-90">
                Nuevo paciente
            </a>
        @endcan
    </div>

    @if (session('status'))
        <p class="mb-4 text-sm text-aura-olive">{{ session('status') }}</p>
    @endif

    <form method="GET" action="{{ route('patients.index') }}" class="mb-6 max-w-sm">
        <input type="text" name="q" value="{{ $search }}" placeholder="Buscar por nombre o teléfono..."
               class="w-full rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">
    </form>

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
                            <a href="{{ route('patients.show', $patient) }}" class="text-aura-olive hover:underline">
                                Ver
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-aura-gray">
                            No se encontraron pacientes.
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
