@props([
    'patient',
    'size' => 'sm',
])

@php
    // La foto es decorativa: el nombre del paciente siempre está presente junto al avatar,
    // por eso el contenedor va con aria-hidden y el <img> con alt vacío (CLAUDE.md §7, WCAG 2.2).
    $variant = [
        'sm' => ['box' => 'h-10 w-10', 'text' => 'text-xs', 'px' => 40],
        'md' => ['box' => 'h-16 w-16', 'text' => 'text-base', 'px' => 64],
        'lg' => ['box' => 'h-20 w-20', 'text' => 'text-lg', 'px' => 80],
    ][$size] ?? ['box' => 'h-10 w-10', 'text' => 'text-xs', 'px' => 40];
@endphp

<span
    aria-hidden="true"
    {{ $attributes->class([
        'relative inline-flex shrink-0 select-none items-center justify-center overflow-hidden rounded-full bg-aura-sage/25 font-medium uppercase text-aura-gray-dark',
        $variant['box'],
        $variant['text'],
    ]) }}
>
    <span>{{ $patient->initials }}</span>

    @if ($patient->hasPhoto())
        {{-- Si la imagen no carga (404 de la ruta autorizada), se retira y quedan visibles las iniciales. --}}
        <img
            src="{{ route('patients.photo', $patient) }}"
            alt=""
            width="{{ $variant['px'] }}"
            height="{{ $variant['px'] }}"
            loading="lazy"
            decoding="async"
            onerror="this.remove()"
            class="absolute inset-0 h-full w-full object-cover"
        >
    @endif
</span>
