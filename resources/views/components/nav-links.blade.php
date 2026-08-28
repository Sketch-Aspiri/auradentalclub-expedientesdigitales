@php
    $link = 'flex items-center gap-3 rounded px-3 py-2 text-sm transition-colors motion-reduce:transition-none focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive';
    $isDashboard = request()->routeIs('dashboard');
    $isConsents = request()->routeIs('consents.*', 'patients.consents.*');
    $isOdontogram = request()->routeIs('odontogram', 'patients.odontogram');
    $isPatients = request()->routeIs('patients.*', 'consultations.*')
        && ! request()->routeIs('patients.odontogram')
        && ! $isConsents;
@endphp

<div class="space-y-1">
    <a href="{{ route('dashboard') }}"
       @class([$link, 'bg-aura-olive text-white' => $isDashboard, 'text-aura-gray-dark hover:bg-aura-cream' => ! $isDashboard])
       @if ($isDashboard) aria-current="page" @endif>
        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 11.5 12 4l9 7.5M5 10v9a1 1 0 0 0 1 1h3v-5h6v5h3a1 1 0 0 0 1-1v-9"/>
        </svg>
        Inicio
    </a>

    <a href="{{ route('patients.index') }}"
       @class([$link, 'bg-aura-olive text-white' => $isPatients, 'text-aura-gray-dark hover:bg-aura-cream' => ! $isPatients])
       @if ($isPatients) aria-current="page" @endif>
        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16 19v-1a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v1M9.5 10.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7ZM17 10.5a3 3 0 0 0-1.7-5.4M21 19v-1a4 4 0 0 0-3-3.85"/>
        </svg>
        Pacientes
    </a>

    <a href="{{ route('odontogram') }}"
       @class([$link, 'bg-aura-olive text-white' => $isOdontogram, 'text-aura-gray-dark hover:bg-aura-cream' => ! $isOdontogram])
       @if ($isOdontogram) aria-current="page" @endif>
        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M12 4.5c-1.6 0-2.6.9-4 .9-1.6 0-3-1-4.3.3C2.3 7 2.6 9.6 3.3 12.6c.4 1.7.6 3.4 1 5 .4 1.7 1 2.9 2 2.9 1.3 0 1.7-1.7 2-3.4.3-1.6.6-3 1.7-3s1.4 1.4 1.7 3c.3 1.7.7 3.4 2 3.4 1 0 1.6-1.2 2-2.9.4-1.6.6-3.3 1-5 .7-3 1-5.6-.4-6.9-1.3-1.3-2.7-.3-4.3-.3-1.4 0-2.4-.9-4-.9Z"/>
        </svg>
        Odontograma
    </a>

    <a href="{{ route('consents.browse') }}"
       @class([$link, 'bg-aura-olive text-white' => $isConsents, 'text-aura-gray-dark hover:bg-aura-cream' => ! $isConsents])
       @if ($isConsents) aria-current="page" @endif>
        <x-icon name="document-check" class="h-5 w-5 shrink-0" />
        Consentimientos
    </a>

    @if (auth()->user()?->isSuperadmin())
        <span class="{{ $link }} text-aura-sage cursor-not-allowed" aria-disabled="true">
            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-2.9 1.2V21a2 2 0 0 1-4 0v-.1A1.7 1.7 0 0 0 7 19.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0-1.2-2.9H3a2 2 0 0 1 0-4h.1A1.7 1.7 0 0 0 4.7 7l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1A1.7 1.7 0 0 0 12 3.1V3a2 2 0 0 1 4 0v.1a1.7 1.7 0 0 0 2.9 1.2l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.9Z"/>
            </svg>
            Configuración del sistema
        </span>
    @endif
</div>
