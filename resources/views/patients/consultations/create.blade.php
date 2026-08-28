{{-- El nombre del paciente no va en el <title> del navegador (historial, pestañas, screen-share): CLAUDE.md §5. --}}
<x-app-layout title="Nueva consulta">
    <div class="max-w-3xl">
        <h1 class="text-lg font-medium mb-1">Nueva consulta</h1>
        <p class="text-sm text-aura-gray mb-6">{{ $patient->full_name }}</p>

        <form method="POST" action="{{ route('patients.consultations.store', $patient) }}" class="space-y-6">
            @csrf

            @include('patients.consultations._form')

            <div class="flex items-center gap-3">
                <button type="submit"
                        class="bg-aura-olive text-white rounded px-4 py-2 text-sm font-medium hover:opacity-90">
                    Guardar consulta
                </button>
                <a href="{{ route('patients.consultations.index', $patient) }}" class="text-sm text-aura-gray hover:text-aura-gray-dark">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
