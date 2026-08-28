{{-- El nombre del paciente no va en el <title> del navegador (historial, pestañas, screen-share): CLAUDE.md §5. --}}
<x-app-layout title="Nuevo consentimiento">
    <div class="max-w-3xl">
        <h1 class="text-lg font-medium mb-1">Nuevo consentimiento</h1>
        <p class="text-sm text-aura-gray mb-6">{{ $patient->full_name }}</p>

        <form method="POST" action="{{ route('patients.consents.store', $patient) }}" class="space-y-6">
            @csrf

            @include('patients.consents._form')

            <div class="flex items-center gap-3">
                <x-button type="submit" variant="primary">Guardar borrador</x-button>
                <a href="{{ route('patients.consents.index', $patient) }}" class="text-sm text-aura-gray hover:text-aura-gray-dark">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
