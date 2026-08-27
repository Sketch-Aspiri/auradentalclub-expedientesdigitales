<x-app-layout :title="'Consultas — '.$patient->full_name">
    <div class="max-w-3xl">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-lg font-medium">Consultas</h1>
                <p class="text-sm text-aura-gray">{{ $patient->full_name }}</p>
            </div>

            @can('create', App\Models\Consultation::class)
                <a href="{{ route('patients.consultations.create', $patient) }}"
                   class="bg-aura-olive text-white rounded px-4 py-2 text-sm font-medium hover:opacity-90">
                    Nueva consulta
                </a>
            @endcan
        </div>

        @if (session('status'))
            <p class="mb-4 text-sm text-aura-olive">{{ session('status') }}</p>
        @endif

        <div class="bg-white border border-aura-gray-light rounded-lg overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-aura-cream text-aura-gray text-xs uppercase tracking-wide">
                    <tr>
                        <th class="text-left px-4 py-3">Fecha</th>
                        <th class="text-left px-4 py-3">Doctor</th>
                        <th class="text-left px-4 py-3">Diagnóstico</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-aura-gray-light">
                    @forelse ($consultations as $consultation)
                        <tr>
                            <td class="px-4 py-3">{{ $consultation->consultation_date->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">{{ $consultation->doctor?->name ?? '—' }}</td>
                            <td class="px-4 py-3">{{ \Illuminate\Support\Str::limit($consultation->clinical_diagnosis, 60) }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('consultations.show', $consultation) }}" class="text-aura-olive hover:underline">
                                    Ver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-aura-gray">
                                Este paciente no tiene consultas registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $consultations->links() }}
        </div>

        @if ($archivedConsultations->isNotEmpty())
            <section class="mt-10">
                <h2 class="text-base font-medium text-aura-gray-dark mb-1">Consultas archivadas</h2>
                <p class="text-sm text-aura-gray-dark mb-3">
                    Restaurar una consulta la devuelve al historial activo del paciente.
                </p>
                <div class="bg-white border border-aura-gray-light rounded-lg overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-aura-cream text-aura-gray text-xs uppercase tracking-wide">
                            <tr>
                                <th class="text-left px-4 py-3">Fecha</th>
                                <th class="text-left px-4 py-3">Doctor</th>
                                <th class="text-left px-4 py-3">Diagnóstico</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-aura-gray-light">
                            @foreach ($archivedConsultations as $consultation)
                                <tr class="text-aura-gray">
                                    <td class="px-4 py-3">{{ $consultation->consultation_date->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3">{{ $consultation->doctor?->name ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ \Illuminate\Support\Str::limit($consultation->clinical_diagnosis, 60) }}</td>
                                    <td class="px-4 py-3 text-right">
                                        @can('restore', $consultation)
                                            <form method="POST" action="{{ route('consultations.restore', $consultation) }}">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit"
                                                        class="inline-flex items-center rounded px-2 py-1 -my-1 text-aura-olive hover:bg-aura-cream focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-aura-olive">
                                                    Restaurar
                                                </button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        <a href="{{ route('patients.show', $patient) }}" class="inline-block mt-6 text-sm text-aura-gray hover:text-aura-gray-dark">
            &larr; Volver a la ficha del paciente
        </a>
    </div>
</x-app-layout>
