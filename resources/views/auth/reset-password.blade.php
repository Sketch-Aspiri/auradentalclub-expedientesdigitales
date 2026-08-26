<x-guest-layout title="Restablecer contraseña">
    <h1 class="text-lg font-medium text-center mb-6">Restablecer contraseña</h1>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label for="email" class="block text-sm text-aura-gray-dark mb-1">Correo electrónico</label>
            <input id="email" type="email" name="email" value="{{ old('email', request('email')) }}" required autofocus
                   class="w-full rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">
            @error('email')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm text-aura-gray-dark mb-1">Nueva contraseña</label>
            <input id="password" type="password" name="password" required
                   class="w-full rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">
            @error('password')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm text-aura-gray-dark mb-1">Confirmar contraseña</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required
                   class="w-full rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">
        </div>

        <button type="submit"
                class="w-full bg-aura-olive text-white rounded py-2 text-sm font-medium hover:opacity-90">
            Restablecer contraseña
        </button>
    </form>
</x-guest-layout>
