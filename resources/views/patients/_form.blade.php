@php
    $patient = $patient ?? null;
    $hasPhoto = (bool) $patient?->hasPhoto();
@endphp

<fieldset id="patient-photo-field" class="border-b border-aura-gray-light pb-6">
    <legend class="text-sm font-medium text-aura-gray-dark">Foto de identificación</legend>
    <p class="mt-1 text-xs text-aura-gray-dark">Parte de la ficha de identificación del expediente. Opcional.</p>

    <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-start">
        <span class="relative flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-full bg-aura-sage/25 text-lg font-medium uppercase text-aura-gray-dark"
              aria-hidden="true">
            <span data-photo-initials>{{ $patient?->initials ?: '—' }}</span>
            <img data-photo-current alt="" width="80" height="80"
                 @if ($hasPhoto) src="{{ route('patients.photo', $patient) }}" @endif
                 @class(['absolute inset-0 h-full w-full object-cover', 'hidden' => ! $hasPhoto])>
            <img data-photo-preview alt="" width="80" height="80"
                 class="absolute inset-0 hidden h-full w-full object-cover">
        </span>

        <div class="min-w-0 flex-1">
            <label for="photo" class="mb-1 block text-sm text-aura-gray-dark">
                {{ $hasPhoto ? 'Reemplazar foto' : 'Subir foto' }}
            </label>
            <input id="photo" type="file" name="photo" accept="image/jpeg,image/png,image/webp"
                   data-photo-input
                   aria-describedby="photo-help @error('photo') photo-error @enderror"
                   class="block w-full text-sm text-aura-gray-dark file:mr-3 file:cursor-pointer file:rounded file:border file:border-aura-gray-light file:bg-white file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-aura-gray-dark hover:file:bg-aura-cream focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive">
            <p id="photo-help" class="mt-1 text-xs text-aura-gray-dark">JPG, PNG o WebP. Máximo 4 MB.</p>
            @error('photo')
                <p id="photo-error" role="alert" class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror

            @if ($hasPhoto)
                <label class="mt-3 inline-flex items-center gap-2 text-sm text-aura-gray-dark">
                    <input type="checkbox" name="remove_photo" value="1" data-photo-remove
                           class="h-4 w-4 rounded border-aura-gray-light accent-aura-olive focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive">
                    Quitar foto actual
                </label>
            @endif
        </div>
    </div>
</fieldset>

<script>
    (function () {
        var field = document.getElementById('patient-photo-field');
        if (!field) return;

        var input = field.querySelector('[data-photo-input]');
        var preview = field.querySelector('[data-photo-preview]');
        var current = field.querySelector('[data-photo-current]');
        var remove = field.querySelector('[data-photo-remove]');
        var objectUrl = null;

        function releaseUrl() {
            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
                objectUrl = null;
            }
        }

        input.addEventListener('change', function () {
            releaseUrl();
            var file = input.files && input.files[0];

            if (!file || file.type.indexOf('image/') !== 0) {
                preview.classList.add('hidden');
                if (current && !(remove && remove.checked)) current.classList.remove('hidden');
                return;
            }

            objectUrl = URL.createObjectURL(file);
            preview.src = objectUrl;
            preview.classList.remove('hidden');
            if (current) current.classList.add('hidden');
            if (remove) remove.checked = false;
        });

        if (remove) {
            remove.addEventListener('change', function () {
                if (remove.checked) {
                    input.value = '';
                    releaseUrl();
                    preview.classList.add('hidden');
                    if (current) current.classList.add('hidden');
                } else if (current) {
                    current.classList.remove('hidden');
                }
            });
        }
    })();
</script>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div class="sm:col-span-2">
        <label for="full_name" class="block text-sm text-aura-gray-dark mb-1">Nombre completo</label>
        <input id="full_name" type="text" name="full_name" value="{{ old('full_name', $patient?->full_name) }}" required
               class="w-full rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">
        @error('full_name')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="birth_date" class="block text-sm text-aura-gray-dark mb-1">Fecha de nacimiento</label>
        <input id="birth_date" type="date" name="birth_date"
               value="{{ old('birth_date', $patient?->birth_date?->format('Y-m-d')) }}" required
               class="w-full rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">
        @error('birth_date')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="sex" class="block text-sm text-aura-gray-dark mb-1">Sexo</label>
        <select id="sex" name="sex" required
                class="w-full rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">
            <option value="">Selecciona...</option>
            <option value="M" @selected(old('sex', $patient?->sex) === 'M')>Masculino</option>
            <option value="F" @selected(old('sex', $patient?->sex) === 'F')>Femenino</option>
        </select>
        @error('sex')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="occupation" class="block text-sm text-aura-gray-dark mb-1">Ocupación</label>
        <input id="occupation" type="text" name="occupation" value="{{ old('occupation', $patient?->occupation) }}"
               class="w-full rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">
        @error('occupation')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="marital_status" class="block text-sm text-aura-gray-dark mb-1">Estado civil</label>
        <input id="marital_status" type="text" name="marital_status" value="{{ old('marital_status', $patient?->marital_status) }}"
               class="w-full rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">
        @error('marital_status')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="address" class="block text-sm text-aura-gray-dark mb-1">Dirección</label>
        <input id="address" type="text" name="address" value="{{ old('address', $patient?->address) }}"
               class="w-full rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">
        @error('address')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="phone" class="block text-sm text-aura-gray-dark mb-1">Teléfono</label>
        <input id="phone" type="text" name="phone" value="{{ old('phone', $patient?->phone) }}" required
               class="w-full rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">
        @error('phone')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="email" class="block text-sm text-aura-gray-dark mb-1">Correo electrónico</label>
        <input id="email" type="email" name="email" value="{{ old('email', $patient?->email) }}"
               class="w-full rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">
        @error('email')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="emergency_contact_name" class="block text-sm text-aura-gray-dark mb-1">Contacto de emergencia</label>
        <input id="emergency_contact_name" type="text" name="emergency_contact_name"
               value="{{ old('emergency_contact_name', $patient?->emergency_contact_name) }}"
               class="w-full rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">
        @error('emergency_contact_name')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="emergency_contact_phone" class="block text-sm text-aura-gray-dark mb-1">Teléfono de emergencia</label>
        <input id="emergency_contact_phone" type="text" name="emergency_contact_phone"
               value="{{ old('emergency_contact_phone', $patient?->emergency_contact_phone) }}"
               class="w-full rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">
        @error('emergency_contact_phone')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>
