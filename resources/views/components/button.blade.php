{{--
    Botón de acción estándar del sistema — mecanismo de feedback de carga #2 (CLAUDE.md
    §10 / .claude/agents/ux-ui-designer.md "Feedback de carga"). Componente obligatorio
    para cualquier botón nuevo que dispare una acción: no escribas <button> sueltos
    repitiendo las clases de color/foco a mano.

    Cubre tres formas distintas de decir "esto está cargando", cada una con su propio
    mecanismo — usa la que corresponda al tipo de acción:

    1. Submit de un <form> Blade clásico (navegación de página completa): no pases nada
       especial. resources/js/button-loader.js detecta el `submit` global y pone el botón
       en estado de carga automáticamente (spinner si hay `icon`, si no solo atenuación +
       `disabled` + `aria-busy`). Cuidado con dos trampas ya resueltas ahí y documentadas
       en ese archivo — no las reintroduzcas si tocas ese script:
         a) deshabilitar el botón de forma síncrona en el evento `submit` hace que el
            navegador NO incluya su `name`/`value` al serializar el formulario;
         b) si el usuario vuelve con el botón "atrás" (bfcache), el botón puede quedar
            deshabilitado para siempre si no se resetea en `pageshow`.
       Este mecanismo requiere que el navegador dispare un `submit` real con un
       `SubmitEvent.submitter` identificable — un botón `type="button"` que solo dispara
       `requestSubmit()` sobre OTRO formulario (ej. el de un modal de confirmación) no
       tiene submitter visible: usa `alpine-loading` para ese caso (ver 3).

    2. Acción Livewire (`wire:click`/`wire:submit`): pasa `wire-target` con el mismo valor
       que usarías en `wire:target="..."` (nombre de la acción, con o sin argumentos). El
       componente añade `wire:loading.attr="disabled"` y alterna icono/spinner con
       `wire:loading`/`wire:loading.remove` scopeados a ese target, para que el spinner no
       se encienda por una acción ajena del mismo componente Livewire. Nota de
       accesibilidad: Livewire solo permite UN `wire:loading.attr="..."` por elemento, así
       que en vez de forzar un `aria-busy` duplicado se usa una región `role="status"
       aria-live="polite"` oculta visualmente que anuncia "Cargando…" mientras dura la
       petición — es el patrón recomendado por Livewire para anunciar carga a lectores de
       pantalla y cubre el mismo propósito que `aria-busy`.

    3. Acción disparada por Alpine sin que el propio botón sea el "submitter" de un
       formulario (ej. el botón "Eliminar" de un modal de confirmación, que hace
       `$refs.form.requestSubmit()` sobre un <form> oculto sin botones dentro — ver punto
       1). Pasa `alpine-loading` con la expresión Alpine booleana que tú declaras y pones
       en `true` en el mismo `@click` que dispara la acción (ej. `alpine-loading="deleting"`
       + `@click="deleting = true; $refs.deleteForm.requestSubmit()"`). El componente hace
       `x-bind:disabled`, `x-bind:aria-busy` y alterna icono/spinner con `x-show` sobre esa
       misma expresión — aquí sí se puede poner `aria-busy` real porque es un atributo de
       Alpine, no una segunda directiva `wire:loading.attr` chocando con la primera.

    Props:
    - variant: 'primary' (bg-aura-olive, acción principal, default), 'secondary' (borde
      neutro, ej. "Cancelar" dentro de un modal), 'danger' (rojo apagado, acciones
      destructivas).
    - type: 'button' (default) | 'submit'.
    - href: si se da, ignora todo lo anterior y renderiza un <a> (para la acción primaria
      de una pantalla que navega, ej. "Nuevo paciente"). Un enlace no tiene estado de
      carga propio — la navegación la cubre el overlay del isotipo
      (resources/js/nav-loader.js) — así que wire-target/alpine-loading no aplican aquí.
    - icon: nombre del catálogo <x-icon> (opcional).
    - wireTarget: ver mecanismo 2.
    - alpineLoading: ver mecanismo 3.
    - loadingText: texto alternativo a mostrar mientras carga (solo aplica a wire-target y
      alpine-loading; si no se da, se repite el texto del slot). Útil para calcar el
      patrón ya usado en el sistema ("Guardar" → "Guardando…").
--}}
@props([
    'variant' => 'primary',
    'type' => 'button',
    'href' => null,
    'icon' => null,
    'wireTarget' => null,
    'alpineLoading' => null,
    'loadingText' => null,
])

@php
    $variantClasses = match ($variant) {
        'secondary' => 'border border-aura-gray-light bg-white text-aura-gray-dark hover:bg-aura-cream focus-visible:ring-aura-olive',
        'danger' => 'bg-red-700 text-white hover:bg-red-800 focus-visible:ring-red-700 focus-visible:ring-offset-2',
        default => 'bg-aura-olive text-white hover:opacity-90 focus-visible:ring-aura-olive focus-visible:ring-offset-2 focus-visible:ring-offset-aura-cream',
    };

    $baseClasses = 'inline-flex min-h-11 items-center justify-center gap-2 rounded-md px-4 py-2 text-sm font-medium '
        .'transition-opacity motion-reduce:transition-none focus-visible:outline-none focus-visible:ring-2 '
        .'disabled:cursor-not-allowed disabled:opacity-70 '.$variantClasses;

    // Mismo spinner ya usado en el buscador de pacientes (livewire/patients/patient-list.blade.php)
    // — se reutiliza tal cual para no introducir un segundo lenguaje visual de "cargando".
    $spinner = '<svg class="h-4 w-4 shrink-0 animate-spin motion-reduce:animate-none" viewBox="0 0 24 24" fill="none" aria-hidden="true">'
        .'<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>'
        .'<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"/></svg>';
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $baseClasses]) }}>
        @if ($icon)
            <x-icon :name="$icon" class="h-4 w-4 shrink-0" />
        @endif
        {{ $slot }}
    </a>
@elseif ($wireTarget)
    <button
        type="{{ $type }}"
        wire:loading.attr="disabled"
        wire:target="{{ $wireTarget }}"
        {{ $attributes->merge(['class' => $baseClasses]) }}
    >
        <span wire:loading.remove wire:target="{{ $wireTarget }}" class="inline-flex items-center gap-2">
            @if ($icon)
                <x-icon :name="$icon" class="h-4 w-4 shrink-0" />
            @endif
            {{ $slot }}
        </span>
        <span wire:loading wire:target="{{ $wireTarget }}" class="inline-flex items-center gap-2" aria-hidden="true">
            {!! $spinner !!}
            {{ $loadingText ?? $slot }}
        </span>
        {{-- Anuncio a lectores de pantalla (ver nota de accesibilidad arriba del archivo):
             equivalente a aria-busy para este caso, porque Livewire no permite apilar dos
             `wire:loading.attr` distintos en el mismo elemento. --}}
        <span class="sr-only" role="status" aria-live="polite" wire:loading wire:target="{{ $wireTarget }}">
            Cargando…
        </span>
    </button>
@elseif ($alpineLoading)
    <button
        type="{{ $type }}"
        x-bind:disabled="{{ $alpineLoading }}"
        x-bind:aria-busy="({{ $alpineLoading }}) ? 'true' : 'false'"
        {{ $attributes->merge(['class' => $baseClasses]) }}
    >
        <span x-show="!({{ $alpineLoading }})" class="inline-flex items-center gap-2">
            @if ($icon)
                <x-icon :name="$icon" class="h-4 w-4 shrink-0" />
            @endif
            {{ $slot }}
        </span>
        <span x-show="{{ $alpineLoading }}" x-cloak class="inline-flex items-center gap-2" aria-hidden="true">
            {!! $spinner !!}
            {{ $loadingText ?? $slot }}
        </span>
    </button>
@else
    {{-- Submit de formulario Blade clásico: resources/js/button-loader.js hace el resto
         (ver mecanismo 1 arriba). data-button-icon/data-button-spinner son los ganchos que
         ese script busca para el intercambio visual; si no hay `icon`, el spinner igual
         aparece durante la carga (sin nada previo que ocultar). --}}
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $baseClasses]) }}>
        @if ($icon)
            <span data-button-icon class="inline-flex shrink-0">
                <x-icon :name="$icon" class="h-4 w-4" />
            </span>
        @endif
        <span data-button-spinner class="hidden shrink-0" aria-hidden="true">
            {!! $spinner !!}
        </span>
        <span>{{ $slot }}</span>
    </button>
@endif
