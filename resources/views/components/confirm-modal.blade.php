{{--
    Modal de confirmación genérico (Alpine + x-teleport), calcado del patrón ya usado en
    resources/views/livewire/patients/odontogram.blade.php (archivar hallazgo). Debe declararse
    dentro del mismo elemento que tiene x-data y expone la variable booleana pasada en $show —
    nunca uses confirm() del navegador para acciones destructivas sobre datos clínicos.

    Convención de refs esperada dentro del slot:
    - x-ref="confirmModalCancel" en el botón de cancelar (recibe el foco al abrir).
    - x-ref="confirmModalConfirm" en el botón de confirmar.
    Ambos deben tener @keydown.tab.prevent hacia el otro para atrapar el foco en el diálogo.

    Props:
    - show: expresión Alpine booleana que controla la visibilidad (ej. "confirmDelete").
    - titleId: id del <h2> del slot, referenciado por aria-labelledby.
    - onClose: expresión Alpine a ejecutar al cancelar/Escape/click fuera. Por defecto pone
      $show en false; pásala explícita si además necesitas devolver el foco al disparador.
--}}
@props([
    'show',
    'titleId',
    'onClose' => null,
])

@php
    $closeExpression = $onClose ?? "{$show} = false";
@endphp

<template x-teleport="body">
    <div
        x-show="{{ $show }}"
        x-cloak
        class="fixed inset-0 z-40 flex items-center justify-center p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="{{ $titleId }}"
        x-transition.opacity.duration.150ms
        x-effect="{{ $show }} && $nextTick(() => $refs.confirmModalCancel && $refs.confirmModalCancel.focus())"
        @keydown.escape.window="{{ $closeExpression }}"
    >
        <div class="absolute inset-0 bg-aura-gray-dark/40" aria-hidden="true" @click="{{ $closeExpression }}"></div>

        <div class="relative w-full max-w-sm rounded-lg border border-aura-gray-light bg-white p-5">
            {{ $slot }}
        </div>
    </div>
</template>
