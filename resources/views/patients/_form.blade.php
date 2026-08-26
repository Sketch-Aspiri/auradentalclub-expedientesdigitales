@php
    $patient = $patient ?? null;
@endphp

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
