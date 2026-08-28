@php
    $item = '-mx-3 flex items-center gap-2 rounded px-3 py-2 text-sm text-aura-gray-dark transition-colors motion-reduce:transition-none hover:bg-aura-cream hover:text-aura-olive focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive';
@endphp

{{--
    `identity`: muestra el avatar + nombre + rol encima de los enlaces. En la barra lateral de
    escritorio va en false porque esa identidad ya vive en la barra superior (`x-user-dropdown`)
    y repetirla en pantalla no aporta nada; en el menú móvil va en true, que es el único lugar
    donde se muestra (la barra superior es `md:block`).
--}}
@props(['identity' => true])

<div class="text-sm">
    @if ($identity)
        <div class="flex items-center gap-2.5">
            <x-user-avatar size="sm" />
            <div class="min-w-0">
                <p class="truncate font-medium text-aura-gray-dark">{{ auth()->user()?->name }}</p>
                <p class="text-xs text-aura-gray">{{ auth()->user()?->role?->label() }}</p>
            </div>
        </div>
    @endif

    <div @class(['space-y-0.5', 'mt-2' => $identity])>
        <a href="{{ route('profile.edit') }}" @class([$item, 'bg-aura-cream text-aura-olive' => request()->routeIs('profile.*')])>
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 19v-1a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v1M12 11a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z"/>
            </svg>
            Mi perfil
        </a>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="{{ $item }} w-full">
                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H3.75m0 0 3.5-3.5M3.75 12l3.5 3.5M10 7.5V6a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-6a2 2 0 0 1-2-2v-1.5"/>
                </svg>
                Cerrar sesión
            </button>
        </form>
    </div>
</div>
