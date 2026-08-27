@props([
    'size' => 'sm',
])

@php
    // Avatar de la cuenta autenticada. Decorativo: el nombre del usuario acompaña siempre
    // al avatar, por eso aria-hidden y alt vacío.
    $user = auth()->user();

    $variant = [
        'xs' => ['box' => 'h-8 w-8', 'text' => 'text-[11px]', 'px' => 32],
        'sm' => ['box' => 'h-9 w-9', 'text' => 'text-xs', 'px' => 36],
        'md' => ['box' => 'h-16 w-16', 'text' => 'text-base', 'px' => 64],
        'lg' => ['box' => 'h-20 w-20', 'text' => 'text-lg', 'px' => 80],
    ][$size] ?? ['box' => 'h-9 w-9', 'text' => 'text-xs', 'px' => 36];
@endphp

<span
    aria-hidden="true"
    {{ $attributes->class([
        'relative inline-flex shrink-0 select-none items-center justify-center overflow-hidden rounded-full bg-aura-olive font-medium uppercase text-white',
        $variant['box'],
        $variant['text'],
    ]) }}
>
    <span>{{ $user?->initials }}</span>

    @if ($user?->hasPhoto())
        {{-- Si la imagen no carga (404 de la ruta autorizada), se retira y quedan las iniciales. --}}
        <img
            src="{{ route('profile.photo') }}"
            alt=""
            width="{{ $variant['px'] }}"
            height="{{ $variant['px'] }}"
            decoding="async"
            onerror="this.remove()"
            class="absolute inset-0 h-full w-full object-cover"
        >
    @endif
</span>
