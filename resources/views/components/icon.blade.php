{{--
    Icono SVG inline reutilizable (estilo heroicons outline, stroke fino). Nunca cargar una
    librería de iconos externa (CLAUDE.md §7) — este componente centraliza los paths usados
    en la interfaz para no repetir <svg> sueltos en cada vista.

    Uso: <x-icon name="pencil" class="h-5 w-5" />
--}}
@props(['name'])

@php
    $icons = [
        'chevron-left' => [
            'viewBox' => '0 0 24 24',
            'strokeWidth' => '1.5',
            'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="M14.5 5 8 12l6.5 7"/>',
        ],
        'clipboard' => [
            'viewBox' => '0 0 24 24',
            'strokeWidth' => '1.5',
            'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5h6a1 1 0 0 1 1 1V6h1.5A1.5 1.5 0 0 1 19 7.5v11a1.5 1.5 0 0 1-1.5 1.5h-11A1.5 1.5 0 0 1 5 18.5v-11A1.5 1.5 0 0 1 6.5 6H8v-.5a1 1 0 0 1 1-1Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6M9 15.5h4"/>',
        ],
        'calendar' => [
            'viewBox' => '0 0 24 24',
            'strokeWidth' => '1.5',
            'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 4v3M16 4v3M4.5 9h15M6 6.5h12A1.5 1.5 0 0 1 19.5 8v10a1.5 1.5 0 0 1-1.5 1.5H6A1.5 1.5 0 0 1 4.5 18V8A1.5 1.5 0 0 1 6 6.5Z"/>',
        ],
        'tooth' => [
            'viewBox' => '0 0 24 24',
            'strokeWidth' => '1.5',
            'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5c-1.6 0-2.6.9-4 .9-1.6 0-3-1-4.3.3C2.3 7 2.6 9.6 3.3 12.6c.4 1.7.6 3.4 1 5 .4 1.7 1 2.9 2 2.9 1.3 0 1.7-1.7 2-3.4.3-1.6.6-3 1.7-3s1.4 1.4 1.7 3c.3 1.7.7 3.4 2 3.4 1 0 1.6-1.2 2-2.9.4-1.6.6-3.3 1-5 .7-3 1-5.6-.4-6.9-1.3-1.3-2.7-.3-4.3-.3-1.4 0-2.4-.9-4-.9Z"/>',
        ],
        'pencil' => [
            'viewBox' => '0 0 24 24',
            'strokeWidth' => '1.5',
            'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="m16.5 4.5 3 3L8 19H5v-3L16.5 4.5Z"/>',
        ],
        'trash' => [
            'viewBox' => '0 0 24 24',
            'strokeWidth' => '1.5',
            'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="M5 7h14M9.5 7V5.5A1.5 1.5 0 0 1 11 4h2a1.5 1.5 0 0 1 1.5 1.5V7M7 7l1 12.5A1.5 1.5 0 0 0 9.5 21h5a1.5 1.5 0 0 0 1.5-1.5L17 7"/>',
        ],
        'check' => [
            'viewBox' => '0 0 20 20',
            'strokeWidth' => '1.75',
            'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="m5 10.5 3.5 3.5L15 6.5"/>',
        ],
        'plus' => [
            'viewBox' => '0 0 24 24',
            'strokeWidth' => '1.75',
            'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>',
        ],
        'magnifying-glass' => [
            'viewBox' => '0 0 24 24',
            'strokeWidth' => '1.75',
            'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10.5 18a7.5 7.5 0 1 0 0-15 7.5 7.5 0 0 0 0 15Z"/><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 20.25-4.65-4.65"/>',
        ],
        'eye' => [
            'viewBox' => '0 0 24 24',
            'strokeWidth' => '1.5',
            'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.04 12.32a1 1 0 0 1 0-.64C3.42 7.51 7.36 4.5 12 4.5c4.64 0 8.58 3.01 9.96 7.18a1 1 0 0 1 0 .64C20.58 16.49 16.64 19.5 12 19.5c-4.64 0-8.58-3.01-9.96-7.18Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>',
        ],
        'archive-box' => [
            'viewBox' => '0 0 24 24',
            'strokeWidth' => '1.5',
            'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.63 10.63a2.25 2.25 0 0 1-2.24 2.12H6.62a2.25 2.25 0 0 1-2.24-2.12L3.75 7.5M10 11.25h4"/><path stroke-linecap="round" stroke-linejoin="round" d="M3.38 7.5h17.25c.62 0 1.12-.5 1.12-1.13v-1.5c0-.62-.5-1.12-1.12-1.12H3.38c-.62 0-1.13.5-1.13 1.12v1.5c0 .63.5 1.13 1.13 1.13Z"/>',
        ],
        'arrow-uturn-left' => [
            'viewBox' => '0 0 24 24',
            'strokeWidth' => '1.5',
            'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/>',
        ],
    ];

    $icon = $icons[$name] ?? null;
@endphp

@if ($icon)
    <svg
        {{ $attributes->merge(['class' => 'h-5 w-5']) }}
        viewBox="{{ $icon['viewBox'] }}"
        fill="none"
        stroke="currentColor"
        stroke-width="{{ $icon['strokeWidth'] }}"
        aria-hidden="true"
    >
        {!! $icon['paths'] !!}
    </svg>
@endif
