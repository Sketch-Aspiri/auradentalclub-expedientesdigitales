{{--
    Lista de condiciones/antecedentes booleanos en modo solo lectura. En vez de mostrar
    checkboxes deshabilitados (poco legible para escanear rápido), muestra únicamente las
    condiciones presentes con un icono de check, y un mensaje explícito cuando no hay ninguna
    — el personal clínico necesita ver de un vistazo si hay algo relevante, no adivinarlo por
    ausencia de marcas.

    Props:
    - items: array de ['label' => string, 'present' => bool].
    - emptyMessage: texto a mostrar cuando ninguna condición está presente.
--}}
@props([
    'items',
    'emptyMessage' => 'Sin antecedentes registrados.',
])

@php
    $present = collect($items)->filter(fn ($item) => $item['present']);
@endphp

@if ($present->isEmpty())
    <p class="text-sm text-aura-gray">{{ $emptyMessage }}</p>
@else
    <ul class="space-y-1.5">
        @foreach ($present as $item)
            <li class="flex items-center gap-2 text-sm text-aura-gray-dark">
                <x-icon name="check" class="h-4 w-4 shrink-0 text-aura-olive" />
                {{ $item['label'] }}
            </li>
        @endforeach
    </ul>
@endif
