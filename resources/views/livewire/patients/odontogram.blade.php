@php
    use App\Enums\ToothStatus;
    use App\Enums\ToothSurface;
    use App\Support\Dentition;

    $anteriorSelected = $selectedTooth !== null && Dentition::isAnterior($selectedTooth);
    $selectedSurfaceEnum = $selectedSurface !== null ? ToothSurface::tryFrom($selectedSurface) : null;
    $toothState = $selectedTooth !== null ? ($this->currentState[$selectedTooth] ?? []) : [];
@endphp

<div class="space-y-6" wire:key="odontogram-{{ $patientId }}">
    @if (session('odontogram-status'))
        <p class="flex items-start gap-2 rounded-md border border-aura-olive/30 bg-aura-olive/5 px-4 py-2.5 text-sm text-aura-olive"
           role="status">
            <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m5 10.5 3.5 3.5L15 6.5"/>
            </svg>
            <span>{{ session('odontogram-status') }}</span>
        </p>
    @endif

    <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_18rem]"
         x-data="{}"
         x-effect="
            $wire.selectedTooth !== null
              && window.matchMedia('(max-width: 1023.98px)').matches
              && (() => {
                   const box = $refs.sidePanel?.getBoundingClientRect();
                   if (box && (box.top < 8 || box.top > window.innerHeight * 0.6)) {
                       $refs.sidePanel.scrollIntoView({
                           behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
                           block: 'start',
                       });
                   }
                 })()
         ">
        {{-- Diagrama --}}
        <div class="rounded-lg border border-aura-gray-light bg-white p-4 sm:p-6">
            <h2 class="sr-only">Diagrama dental</h2>

            <div class="space-y-8 overflow-x-auto overscroll-x-contain rounded print:overflow-visible
                        focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive focus-visible:ring-offset-2"
                 tabindex="0" role="group"
                 aria-label="Diagrama dental. Desplázate horizontalmente para ver todas las piezas.">
                @foreach ($arches as $archLabel => $halves)
                    <div class="print:break-inside-avoid">
                        <p class="mb-3 text-xs font-medium uppercase tracking-wide text-aura-gray">{{ $archLabel }}</p>
                        <div class="flex items-start gap-2 sm:gap-3">
                            @foreach ($halves as $halfIndex => $teeth)
                                @if ($halfIndex === 1)
                                    <div class="self-stretch border-l border-dashed border-aura-gray-light" aria-hidden="true"></div>
                                @endif
                                <div class="flex gap-1 sm:gap-1.5">
                                    @foreach ($teeth as $tooth)
                                        <x-odontogram.tooth
                                            :number="$tooth"
                                            :state="$this->currentState[$tooth] ?? []"
                                            :selected-tooth="$selectedTooth"
                                            :selected-surface="$selectedSurface"
                                        />
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <p class="mt-3 text-xs text-aura-gray lg:hidden">
                Desliza el diagrama en horizontal para ver toda la arcada.
            </p>

            {{-- Leyenda --}}
            <div class="mt-8 border-t border-aura-gray-light pt-4">
                <h3 class="mb-2 text-xs font-medium uppercase tracking-wide text-aura-gray">Referencia</h3>
                <ul class="flex flex-wrap gap-x-4 gap-y-2 text-xs text-aura-gray-dark">
                    @foreach ($statusCatalog as $item)
                        @continue($item['value'] === ToothStatus::Sano->value)
                        <li class="flex items-center gap-1.5">
                            <span class="inline-block h-3 w-3 rounded-sm border border-aura-gray-light"
                                  style="background-color: {{ $item['color'] }}" aria-hidden="true"></span>
                            {{ $item['label'] }}
                        </li>
                    @endforeach
                    <li class="flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5 shrink-0 stroke-aura-gray" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                            <rect x="1.5" y="1.5" width="13" height="13" stroke-width="2"/>
                        </svg>
                        Marco = estado de todo el diente
                    </li>
                    <li class="flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5 shrink-0 stroke-aura-gray" viewBox="0 0 16 16" fill="none" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                            <path d="M3 3 13 13M13 3 3 13"/>
                        </svg>
                        Cruz = pieza extraída o ausente
                    </li>
                </ul>
            </div>
        </div>

        {{-- Panel lateral: pieza seleccionada --}}
        <div x-ref="sidePanel" class="lg:sticky lg:top-6 lg:self-start print:static">
            <div class="rounded-lg border border-aura-gray-light bg-white p-4 transition-opacity sm:p-5"
                 x-data="{ archiveId: null, archiveLabel: '', closeArchive() { this.archiveId = null; this.$nextTick(() => this.$refs.historyHeading?.focus()); } }"
                 wire:loading.class="opacity-50"
                 wire:target="select, save, deleteRecord, restoreRecord">
                @if ($selectedTooth === null)
                    <div class="flex flex-col items-center px-2 py-6 text-center">
                        <span class="mb-3 inline-flex h-14 w-14 items-center justify-center rounded-full bg-aura-cream" aria-hidden="true">
                            <svg class="h-8 w-8 stroke-aura-sage" viewBox="0 0 24 24" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 4.5c-1.6 0-2.6.9-4 .9-1.6 0-3-1-4.3.3C2.3 7 2.6 9.6 3.3 12.6c.4 1.7.6 3.4 1 5 .4 1.7 1 2.9 2 2.9 1.3 0 1.7-1.7 2-3.4.3-1.6.6-3 1.7-3s1.4 1.4 1.7 3c.3 1.7.7 3.4 2 3.4 1 0 1.6-1.2 2-2.9.4-1.6.6-3.3 1-5 .7-3 1-5.6-.4-6.9-1.3-1.3-2.7-.3-4.3-.3-1.4 0-2.4-.9-4-.9Z"/>
                            </svg>
                        </span>
                        <p class="text-sm text-aura-gray-dark">
                            Selecciona un diente para ver su historial y registrar un hallazgo.
                        </p>
                        <p class="mt-1.5 text-xs text-aura-gray">
                            Toca el número para el diente completo, o una zona del esquema para una superficie.
                        </p>
                    </div>
                @else
                    <div class="flex items-center justify-between gap-2">
                        <h2 class="text-base font-medium text-aura-gray-dark">Diente {{ $selectedTooth }}</h2>
                        <button type="button" wire:click="clearSelection"
                                class="inline-flex min-h-6 items-center rounded px-2 py-1 text-xs text-aura-gray-dark transition-colors hover:bg-aura-cream focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive">
                            Cerrar
                        </button>
                    </div>

                    {{-- Selector de alcance: diente completo o superficie --}}
                    <div class="mt-3 flex flex-wrap gap-1.5" role="group" aria-label="Alcance del hallazgo">
                        <button type="button" wire:click="select({{ $selectedTooth }})"
                                aria-pressed="{{ $selectedSurface === null ? 'true' : 'false' }}"
                                @class([
                                    'inline-flex min-h-6 items-center rounded px-2.5 py-1 text-xs transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive',
                                    'bg-aura-olive text-white' => $selectedSurface === null,
                                    'bg-aura-cream text-aura-gray-dark hover:bg-aura-gray-light' => $selectedSurface !== null,
                                ])>
                            Diente completo
                        </button>
                        @foreach (ToothSurface::cases() as $surface)
                            <button type="button" wire:click="select({{ $selectedTooth }}, '{{ $surface->value }}')"
                                    aria-pressed="{{ $selectedSurface === $surface->value ? 'true' : 'false' }}"
                                    @class([
                                        'inline-flex min-h-6 items-center rounded px-2.5 py-1 text-xs transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive',
                                        'bg-aura-olive text-white' => $selectedSurface === $surface->value,
                                        'bg-aura-cream text-aura-gray-dark hover:bg-aura-gray-light' => $selectedSurface !== $surface->value,
                                    ])>
                                {{ $surface->label($anteriorSelected) }}
                            </button>
                        @endforeach
                    </div>

                    <p class="mt-2 text-xs text-aura-gray">
                        Registrando en:
                        <span class="font-medium text-aura-gray-dark">
                            {{ $selectedSurfaceEnum?->label($anteriorSelected) ?? 'diente completo' }}
                        </span>
                    </p>

                    {{-- Formulario de hallazgo --}}
                    <form wire:submit="save" class="mt-4 space-y-3">
                        <h3 class="text-xs font-medium uppercase tracking-wide text-aura-gray">Registrar hallazgo</h3>

                        <div>
                            <label for="odontogram-status" class="block text-xs font-medium text-aura-gray-dark">Estado</label>
                            <select id="odontogram-status" wire:model="status"
                                    @error('status') aria-invalid="true" aria-describedby="odontogram-status-error" @enderror
                                    class="mt-1 w-full rounded-md border-aura-gray-light text-sm focus:border-aura-olive focus:ring-aura-olive">
                                <option value="">Selecciona un estado…</option>
                                @foreach ($statusCatalog as $item)
                                    @if (($selectedSurface !== null && $item['surface']) || ($selectedSurface === null && $item['whole']))
                                        <option value="{{ $item['value'] }}">{{ $item['label'] }}</option>
                                    @endif
                                @endforeach
                            </select>
                            @error('status')
                                <p id="odontogram-status-error" class="mt-1 text-xs text-red-700" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="odontogram-date" class="block text-xs font-medium text-aura-gray-dark">Fecha del hallazgo</label>
                            <input type="date" id="odontogram-date" wire:model="recordedAt" max="{{ now()->toDateString() }}"
                                   @error('recordedAt') aria-invalid="true" aria-describedby="odontogram-date-error" @enderror
                                   class="mt-1 w-full rounded-md border-aura-gray-light text-sm focus:border-aura-olive focus:ring-aura-olive">
                            @error('recordedAt')
                                <p id="odontogram-date-error" class="mt-1 text-xs text-red-700" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="odontogram-note" class="block text-xs font-medium text-aura-gray-dark">Nota (opcional)</label>
                            <textarea id="odontogram-note" wire:model="note" rows="2" maxlength="2000"
                                      @error('note') aria-invalid="true" aria-describedby="odontogram-note-error" @enderror
                                      class="mt-1 w-full rounded-md border-aura-gray-light text-sm focus:border-aura-olive focus:ring-aura-olive"></textarea>
                            @error('note')
                                <p id="odontogram-note-error" class="mt-1 text-xs text-red-700" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <x-button type="submit" variant="primary" wire-target="save" loading-text="Registrando…" class="w-full">
                            Registrar hallazgo
                        </x-button>
                    </form>

                    {{-- Estado vigente de la pieza --}}
                    @php
                        $toothIsGone = ($toothState['whole'] ?? null)?->meansToothIsGone() ?? false;
                        // Si la pieza está fuera de boca, solo tiene sentido su estado de diente completo.
                        $visibleState = $toothIsGone
                            ? array_intersect_key($toothState, ['whole' => true])
                            : $toothState;
                    @endphp
                    @if (! empty($visibleState))
                        <div class="mt-5 border-t border-aura-gray-light pt-4">
                            <h3 class="mb-2 text-xs font-medium uppercase tracking-wide text-aura-gray">Estado vigente</h3>
                            <ul class="space-y-1 text-xs text-aura-gray-dark">
                                @foreach ($visibleState as $key => $st)
                                    <li class="flex items-center gap-1.5">
                                        <span class="inline-block h-2.5 w-2.5 shrink-0 rounded-sm border border-aura-gray-light"
                                              style="background-color: {{ $st->color() }}" aria-hidden="true"></span>
                                        <span class="font-medium">
                                            {{ $key === 'whole' ? 'Diente completo' : ToothSurface::from($key)->label($anteriorSelected) }}:
                                        </span>
                                        {{ $st->label() }}
                                    </li>
                                @endforeach
                            </ul>
                            @if ($toothIsGone)
                                <p class="mt-2 text-xs text-aura-gray">Los hallazgos de superficie previos quedan en el historial.</p>
                            @endif
                        </div>
                    @endif

                    {{-- Historial --}}
                    <div class="mt-5 border-t border-aura-gray-light pt-4">
                        <h3 x-ref="historyHeading" tabindex="-1"
                            class="mb-2 text-xs font-medium uppercase tracking-wide text-aura-gray focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive">
                            Historial de la pieza
                        </h3>
                        @forelse ($this->selectedToothHistory as $record)
                            <div class="flex items-start justify-between gap-2 border-b border-aura-gray-light/60 py-2 text-xs last:border-0"
                                 wire:key="history-{{ $record->id }}">
                                <div>
                                    <p class="font-medium text-aura-gray-dark">
                                        {{ $record->status->label() }}
                                        <span class="font-normal text-aura-gray">
                                            · {{ $record->surface?->label($anteriorSelected) ?? 'diente completo' }}
                                        </span>
                                    </p>
                                    <p class="text-aura-gray">
                                        {{ $record->recorded_at->translatedFormat('d M Y') }} ·
                                        {{ $record->recordedBy?->name ?? 'Usuario eliminado' }}
                                    </p>
                                    @if ($record->note)
                                        <p class="mt-0.5 text-aura-gray-dark">{{ $record->note }}</p>
                                    @endif
                                </div>
                                @can('delete', $record)
                                    <button type="button"
                                            @click="archiveId = {{ $record->id }}; archiveLabel = @js($record->status->label().' · '.($record->surface?->label($anteriorSelected) ?? 'diente completo'))"
                                            class="inline-flex min-h-6 shrink-0 items-center rounded px-2 py-1 text-xs font-medium text-red-700 transition-colors hover:bg-red-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive">
                                        Archivar
                                    </button>
                                @endcan
                            </div>
                        @empty
                            <p class="text-xs text-aura-gray">Sin hallazgos registrados en esta pieza.</p>
                        @endforelse
                    </div>

                    {{-- Hallazgos archivados de la pieza --}}
                    @if ($this->archivedToothHistory->isNotEmpty())
                        <div class="mt-4 border-t border-aura-gray-light pt-4">
                            <h3>
                                <button type="button" wire:click="toggleArchived"
                                        aria-expanded="{{ $showArchived ? 'true' : 'false' }}"
                                        class="flex w-full items-center justify-between rounded py-1 text-xs font-medium uppercase tracking-wide text-aura-gray transition-colors hover:text-aura-gray-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive">
                                    <span>Archivados ({{ $this->archivedToothHistory->count() }})</span>
                                    <svg class="h-4 w-4 shrink-0 transition-transform duration-150 motion-reduce:transition-none {{ $showArchived ? 'rotate-45' : '' }}"
                                         viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                        <path stroke-linecap="round" d="M8 3.5v9M3.5 8h9"/>
                                    </svg>
                                </button>
                            </h3>

                            @if ($showArchived)
                                <p class="mt-2 text-xs text-aura-gray">
                                    Restaurar un hallazgo lo devuelve al historial activo y vuelve a contar para el estado vigente.
                                </p>
                                <div class="mt-1">
                                    @foreach ($this->archivedToothHistory as $record)
                                        <div class="flex items-start justify-between gap-2 border-b border-aura-gray-light/60 py-2 text-xs last:border-0"
                                             wire:key="archived-{{ $record->id }}">
                                            <div>
                                                <p class="font-medium text-aura-gray">
                                                    {{ $record->status->label() }}
                                                    <span class="font-normal">
                                                        · {{ $record->surface?->label($anteriorSelected) ?? 'diente completo' }}
                                                    </span>
                                                </p>
                                                <p class="text-aura-gray">
                                                    {{ $record->recorded_at->translatedFormat('d M Y') }} ·
                                                    {{ $record->recordedBy?->name ?? 'Usuario eliminado' }}
                                                </p>
                                                @if ($record->note)
                                                    <p class="mt-0.5 text-aura-gray">{{ $record->note }}</p>
                                                @endif
                                            </div>
                                            @can('restore', $record)
                                                <button type="button"
                                                        wire:click="restoreRecord({{ $record->id }})"
                                                        class="inline-flex min-h-6 shrink-0 items-center rounded px-2 py-1 font-medium text-aura-olive transition-colors hover:bg-aura-cream focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive">
                                                    Restaurar
                                                </button>
                                            @endcan
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Confirmación de archivado (modal de la propia UI, sin diálogo del navegador) --}}
                    <template x-teleport="body">
                        <div x-show="archiveId !== null" x-cloak
                             class="fixed inset-0 z-40 flex items-center justify-center p-4"
                             role="dialog" aria-modal="true" aria-labelledby="odontogram-archive-title"
                             x-transition.opacity.duration.150ms
                             x-effect="archiveId !== null && $nextTick(() => $refs.cancelArchive && $refs.cancelArchive.focus())"
                             @keydown.escape.window="closeArchive()">
                            <div class="absolute inset-0 bg-aura-gray-dark/40" aria-hidden="true" @click="closeArchive()"></div>

                            <div class="relative w-full max-w-sm rounded-lg border border-aura-gray-light bg-white p-5">
                                <h2 id="odontogram-archive-title" class="text-base font-medium text-aura-gray-dark">
                                    Archivar hallazgo
                                </h2>
                                <p class="mt-2 text-sm text-aura-gray">
                                    Se archivará <span class="font-medium text-aura-gray-dark" x-text="archiveLabel"></span>.
                                    Sale del estado vigente pero se conserva en la auditoría y puede restaurarse.
                                </p>
                                <div class="mt-5 flex justify-end gap-2">
                                    <button type="button" x-ref="cancelArchive"
                                            @click="closeArchive()"
                                            @keydown.tab.prevent="$refs.confirmArchive.focus()"
                                            class="rounded-md border border-aura-gray-light px-3 py-2 text-sm text-aura-gray-dark transition-colors hover:bg-aura-cream focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive">
                                        Cancelar
                                    </button>
                                    <button type="button" x-ref="confirmArchive"
                                            @click="$wire.deleteRecord(archiveId); closeArchive()"
                                            @keydown.tab.prevent="$refs.cancelArchive.focus()"
                                            class="rounded-md bg-red-700 px-3 py-2 text-sm font-medium text-white transition-colors hover:bg-red-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-700 focus-visible:ring-offset-2">
                                        Archivar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                @endif
            </div>
        </div>
    </div>
</div>
