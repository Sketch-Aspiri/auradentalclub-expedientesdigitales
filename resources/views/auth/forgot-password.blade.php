<x-guest-layout title="Recuperar contraseña">
    <h1 class="text-lg font-medium text-center mb-2">Recuperar contraseña</h1>
    <p class="text-sm text-aura-gray text-center mb-6">
        Ingresa tu correo y te enviaremos un enlace para restablecer tu contraseña.
    </p>

    @if (session('status'))
        <p class="mb-4 text-sm text-aura-olive text-center">{{ session('status') }}</p>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-sm text-aura-gray-dark mb-1">Correo electrónico</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="w-full rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">
            @error('email')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
                class="w-full bg-aura-olive text-white rounded py-2 text-sm font-medium hover:opacity-90">
            Enviar enlace
        </button>

        <p class="text-center text-sm">
            <a href="{{ route('login') }}" class="text-aura-gray hover:text-aura-gray-dark hover:underline">
                Volver a iniciar sesión
            </a>
        </p>
    </form>
</x-guest-layout>
