<form wire:submit="submit" class="space-y-6">
    <p class="rounded-md border border-aura-gray-light bg-white px-4 py-3 text-sm text-aura-gray">
        Firma en cada recuadro con el dedo o el mouse. Al firmar, el consentimiento queda
        <span class="font-medium text-aura-gray-dark">bloqueado</span>: para corregirlo tendrás que anularlo y crear uno nuevo.
    </p>

    @foreach ([
        ['patientSignature', 'Firma del paciente o de quien otorga el consentimiento', true],
        ['doctorSignature', 'Firma del médico', true],
    ] as [$prop, $label, $required])
        <div class="bg-white border border-aura-gray-light rounded-lg p-4">
            <p class="text-sm font-medium text-aura-gray-dark">
                {{ $label }} @if ($required) <span class="text-red-600">*</span> @endif
            </p>
            <div x-data="signatureCanvas($wire, '{{ $prop }}')" class="mt-2">
                <canvas x-ref="canvas" role="img" aria-label="{{ $label }}"
                        class="h-40 w-full touch-none rounded border border-dashed border-aura-gray-light bg-white"></canvas>
                <div class="mt-2 flex justify-end">
                    <button type="button" @click="clear()"
                            class="inline-flex items-center gap-1 rounded px-2 py-1 text-xs font-medium text-aura-gray-dark hover:bg-aura-cream focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive">
                        Borrar firma
                    </button>
                </div>
            </div>
            @error($prop) <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    @endforeach

    @foreach ([
        ['witness1Name', 'witness1Signature', 'Primer testigo'],
        ['witness2Name', 'witness2Signature', 'Segundo testigo'],
    ] as [$nameProp, $sigProp, $label])
        <div class="bg-white border border-aura-gray-light rounded-lg p-4 space-y-3">
            <p class="text-sm font-medium text-aura-gray-dark">{{ $label }} <span class="text-xs font-normal text-aura-gray">(opcional)</span></p>
            <div>
                <label for="{{ $nameProp }}" class="block text-xs text-aura-gray-dark mb-1">Nombre completo</label>
                <input id="{{ $nameProp }}" type="text" wire:model="{{ $nameProp }}" maxlength="255"
                       class="w-full rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">
                @error($nameProp) <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div x-data="signatureCanvas($wire, '{{ $sigProp }}')">
                <canvas x-ref="canvas" role="img" aria-label="Firma de {{ $label }}"
                        class="h-40 w-full touch-none rounded border border-dashed border-aura-gray-light bg-white"></canvas>
                <div class="mt-2 flex justify-end">
                    <button type="button" @click="clear()"
                            class="inline-flex items-center gap-1 rounded px-2 py-1 text-xs font-medium text-aura-gray-dark hover:bg-aura-cream focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive">
                        Borrar firma
                    </button>
                </div>
                @error($sigProp) <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>
    @endforeach

    <div class="flex items-center gap-3">
        <x-button type="submit" variant="primary" wire-target="submit" loading-text="Firmando…">
            Firmar consentimiento
        </x-button>
        <a href="{{ route('consents.show', $consent) }}" class="text-sm text-aura-gray hover:text-aura-gray-dark">
            Cancelar
        </a>
    </div>
</form>
