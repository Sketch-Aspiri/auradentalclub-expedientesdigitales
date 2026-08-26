<x-guest-layout title="Iniciar sesión">
    <h1 class="text-lg font-medium text-center mb-6">Iniciar sesión</h1>

    @if (session('status'))
        <p class="mb-4 text-sm text-aura-olive text-center">{{ session('status') }}</p>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-sm text-aura-gray-dark mb-1">Correo electrónico</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="w-full rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">
            @error('email')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm text-aura-gray-dark mb-1">Contraseña</label>
            <input id="password" type="password" name="password" required
                   class="w-full rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">
            @error('password')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between text-sm">
            <label class="flex items-center gap-2 text-aura-gray">
                <input type="checkbox" name="remember" class="rounded border-aura-gray-light">
                Recordarme
            </label>

            <a href="{{ route('password.request') }}" class="text-aura-olive hover:underline">
                ¿Olvidaste tu contraseña?
            </a>
        </div>

        <button type="submit"
                class="w-full bg-aura-olive text-white rounded py-2 text-sm font-medium hover:opacity-90">
            Entrar
        </button>
    </form>
</x-guest-layout>
