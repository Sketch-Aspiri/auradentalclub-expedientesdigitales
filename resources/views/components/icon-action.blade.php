{{--
    Botón/enlace de acción con solo icono + tooltip, para reemplazar filas de links de texto
    en encabezados de expediente (CLAUDE.md §7 — "iconos en vez de palabras"). Sigue siendo
    accesible: aria-label + title dan el nombre accesible, el <span> visual es solo decorativo.

    Props:
    - label: texto del nombre accesible y del tooltip (obligatorio, en español).
    - href: si se da, renderiza <a>; si no, renderiza <button type="button"> (para abrir un
      modal Alpine u otra interacción, ej. @click="confirmDelete = true").
    - tone: 'default' (texto/hover oliva) o 'danger' (texto/hover rojo apagado) para acciones
      destructivas — el anillo de foco siempre es aura-olive (regla de marca, no de tono).
--}}
@props([
    'label',
    'href' => null,
    'tone' => 'default',
])

@php
    $toneClasses = $tone === 'danger'
        ? 'text-aura-gray-dark hover:bg-red-50 hover:text-red-700'
        : 'text-aura-gray-dark hover:bg-aura-cream hover:text-aura-olive';

    $baseClasses = "group relative inline-flex h-11 w-11 items-center justify-center rounded-md transition-colors motion-reduce:transition-none focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive {$toneClasses}";
@endphp

@if ($href)
    <a href="{{ $href }}" aria-label="{{ $label }}" title="{{ $label }}" {{ $attributes->merge(['class' => $baseClasses]) }}>
        {{ $slot }}
        <span aria-hidden="true" class="pointer-events-none absolute left-1/2 top-full z-10 mt-2 -translate-x-1/2 whitespace-nowrap rounded bg-aura-gray-dark px-2 py-1 text-xs text-white opacity-0 transition-opacity duration-150 motion-reduce:transition-none group-hover:opacity-100 group-focus-visible:opacity-100">
            {{ $label }}
        </span>
    </a>
@else
    <button type="button" aria-label="{{ $label }}" title="{{ $label }}" {{ $attributes->merge(['class' => $baseClasses]) }}>
        {{ $slot }}
        <span aria-hidden="true" class="pointer-events-none absolute left-1/2 top-full z-10 mt-2 -translate-x-1/2 whitespace-nowrap rounded bg-aura-gray-dark px-2 py-1 text-xs text-white opacity-0 transition-opacity duration-150 motion-reduce:transition-none group-hover:opacity-100 group-focus-visible:opacity-100">
            {{ $label }}
        </span>
    </button>
@endif
