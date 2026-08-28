{{-- El nombre del paciente no va en el <title> del navegador (historial, pestañas, screen-share): CLAUDE.md §5. --}}
<x-app-layout title="Firmar consentimiento">
    <div class="max-w-2xl">
        <a href="{{ route('consents.show', $consent) }}"
           class="inline-flex min-h-11 items-center gap-1.5 rounded text-sm text-aura-gray-dark transition-colors motion-reduce:transition-none hover:text-aura-olive focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive">
            <x-icon name="chevron-left" class="h-4 w-4" />
            Volver al consentimiento
        </a>

        <h1 class="mt-6 text-lg font-medium">Firmar consentimiento — {{ $consent->type->label() }}</h1>
        <p class="text-sm text-aura-gray">{{ $patient->full_name }} · Dr(a). {{ $consent->doctor?->name ?? '—' }}</p>

        <div class="mt-6">
            <livewire:consents.sign-consent :consent="$consent" />
        </div>
    </div>
</x-app-layout>
