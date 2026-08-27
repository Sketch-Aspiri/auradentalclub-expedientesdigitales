@props([
    'number',
    'state' => [],
    'selectedTooth' => null,
    'selectedSurface' => null,
])

@php
    use App\Enums\ToothStatus;
    use App\Enums\ToothSurface;
    use App\Support\Dentition;

    $layout = Dentition::surfaceLayout($number);
    $anterior = Dentition::isAnterior($number);

    $whole = $state['whole'] ?? null;
    $toothGone = $whole?->meansToothIsGone() ?? false;
    $isAusente = $whole === ToothStatus::Ausente;

    $isSelectedTooth = (int) $selectedTooth === (int) $number;
    $isWholeSelected = $isSelectedTooth && $selectedSurface === null;

    // Resumen de estado para lectores de pantalla: el SVG es decorativo (aria-hidden),
    // el botón del número es el único punto de entrada accesible de la pieza.
    $summary = [];
    if ($whole) {
        $summary[] = mb_strtolower($whole->label());
    }
    if (! $toothGone) {
        foreach ($state as $key => $st) {
            if ($key === 'whole') {
                continue;
            }
            $summary[] = mb_strtolower(ToothSurface::from($key)->label($anterior).' '.$st->label());
        }
    }
    $srState = $summary === []
        ? 'Diente '.$number.', sin hallazgos registrados'
        : 'Diente '.$number.': '.implode(', ', $summary);

    // Geometría de las 5 zonas (viewBox 0 0 44 44, cuadro central 15–29).
    $zones = [
        'top'    => ['shape' => 'polygon', 'points' => '2,2 42,2 29,15 15,15'],
        'right'  => ['shape' => 'polygon', 'points' => '42,2 42,42 29,29 29,15'],
        'bottom' => ['shape' => 'polygon', 'points' => '2,42 42,42 29,29 15,29'],
        'left'   => ['shape' => 'polygon', 'points' => '2,2 15,15 15,29 2,42'],
        'center' => ['shape' => 'rect'],
    ];

    // El color/grosor del trazo va en clases Tailwind (tokens aura-*), no en atributos de
    // presentación SVG: los navegadores no resuelven var(--color-*) dentro de un atributo.
    $zoneClass = 'cursor-pointer transition-[stroke,stroke-width] duration-150 motion-reduce:transition-none '
        .'hover:stroke-aura-olive hover:[stroke-width:2]';
@endphp

<div class="flex flex-col items-center gap-1" wire:key="tooth-{{ $number }}">
    <button
        type="button"
        wire:click="select({{ $number }})"
        aria-pressed="{{ $isWholeSelected ? 'true' : 'false' }}"
        aria-label="{{ $srState }}"
        title="Diente {{ $number }}"
        class="flex h-6 min-w-6 items-center justify-center rounded px-1 text-[11px] font-semibold tabular-nums leading-none
               transition-colors motion-reduce:transition-none
               focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive focus-visible:ring-offset-1
               {{ $isSelectedTooth ? 'bg-aura-olive text-white' : 'text-aura-gray-dark hover:bg-aura-cream' }}"
    >
        {{ $number }}
    </button>

    <svg viewBox="0 0 44 44"
         class="h-11 w-11 shrink-0 sm:h-12 sm:w-12 {{ $toothGone ? 'opacity-70' : '' }}"
         aria-hidden="true" focusable="false">
        @foreach ($zones as $position => $zone)
            @php
                $surface = $layout[$position];
                $current = $state[$surface->value] ?? null;
                $fill = $current?->color() ?? '#FFFFFF';
                $isSelectedZone = $isSelectedTooth && $selectedSurface === $surface->value;
                $strokeClass = $isSelectedZone
                    ? 'stroke-aura-olive [stroke-width:2.5]'
                    : 'stroke-aura-gray [stroke-width:1]';
            @endphp

            @if ($zone['shape'] === 'rect')
                <rect x="15" y="15" width="14" height="14"
                      fill="{{ $fill }}"
                      class="{{ $strokeClass }} {{ $zoneClass }}"
                      wire:click.stop="select({{ $number }}, '{{ $surface->value }}')">
                    <title>Diente {{ $number }} · {{ $surface->label($anterior) }}{{ $current ? ' · '.$current->label() : '' }}</title>
                </rect>
            @else
                <polygon points="{{ $zone['points'] }}"
                         fill="{{ $fill }}"
                         class="{{ $strokeClass }} {{ $zoneClass }}"
                         wire:click.stop="select({{ $number }}, '{{ $surface->value }}')">
                    <title>Diente {{ $number }} · {{ $surface->label($anterior) }}{{ $current ? ' · '.$current->label() : '' }}</title>
                </polygon>
            @endif
        @endforeach

        @if ($whole && ! $toothGone)
            {{-- Estado de diente completo (corona, endodoncia, implante...): marco de color. --}}
            <rect x="1.5" y="1.5" width="41" height="41" fill="none"
                  stroke="{{ $whole->color() }}" stroke-width="2.5" />
        @endif

        @if ($toothGone)
            {{-- Extraído: X sólida. Ausente (nunca erupcionó): X punteada, para distinguirlas sin depender del color. --}}
            <path d="M7 7 L37 37 M37 7 L7 37"
                  stroke="{{ $whole->color() }}" stroke-width="3" stroke-linecap="round"
                  @if ($isAusente) stroke-dasharray="4 3" @endif />
        @endif

        @if ($isSelectedTooth)
            <rect x="0.75" y="0.75" width="42.5" height="42.5" fill="none"
                  class="stroke-aura-olive [stroke-width:1.5]" stroke-dasharray="3 2" />
        @endif
    </svg>
</div>
