<x-guest-layout title="Iniciar sesión">
    <x-slot:aside>
        <img src="{{ asset('logos/logo_completo.png') }}" alt="aura dental club"
             width="3604" height="1394" loading="eager"
             class="relative z-10 h-16 w-auto max-w-[340px] shrink-0 self-start brightness-0 invert lg:h-20">

        <div class="relative z-10 max-w-xs">
            <p class="text-2xl font-light leading-snug tracking-tight">
                El expediente clínico de tus pacientes, claro y bien resguardado.
            </p>
            <p class="mt-3 text-sm leading-relaxed text-aura-cream">
                Consultas, diagnósticos, odontograma y consentimientos de Aura Dental Club, reunidos en un solo lugar y con acceso controlado.
            </p>
        </div>

        <img src="{{ asset('logos/monograma.png') }}" alt="" aria-hidden="true"
             width="2502" height="2466"
             class="pointer-events-none absolute -bottom-24 -right-16 w-80 max-w-none select-none opacity-10 brightness-0 invert">
    </x-slot:aside>

    <div class="mx-auto w-full max-w-sm">
        <img src="{{ asset('logos/logo_completo.png') }}" alt="aura dental club"
             width="3604" height="1394" loading="eager"
             class="h-12 w-auto md:hidden">

        <div class="mt-8 md:mt-0">
            <h1 class="text-xl font-medium text-aura-gray-dark">Iniciar sesión</h1>
            <p class="mt-2 text-sm leading-relaxed text-aura-gray">
                Acceso al expediente clínico digital de Aura Dental Club. Uso exclusivo del personal autorizado.
            </p>
        </div>

        @if (session('status'))
            <p class="mt-4 text-sm text-aura-olive" role="status">{{ session('status') }}</p>
        @endif

        <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
            @csrf

            <div>
                <label for="email" class="mb-1 block text-sm text-aura-gray-dark">Correo electrónico</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                       autocomplete="email"
                       @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                       class="w-full rounded border border-aura-gray-light px-3 py-2 text-sm text-aura-gray-dark focus:border-aura-olive focus:outline-none focus:ring-1 focus:ring-aura-olive">
                @error('email')
                    <p id="email-error" role="alert" class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="mb-1 block text-sm text-aura-gray-dark">Contraseña</label>
                <div class="relative">
                    <input id="password" type="password" name="password" required
                           autocomplete="current-password"
                           @error('password') aria-invalid="true" aria-describedby="password-error" @enderror
                           class="w-full rounded border border-aura-gray-light px-3 py-2 pr-11 text-sm text-aura-gray-dark focus:border-aura-olive focus:outline-none focus:ring-1 focus:ring-aura-olive">
                    <button type="button" id="toggle-password" aria-pressed="false" aria-label="Mostrar contraseña"
                            class="absolute inset-y-0 right-1 my-auto inline-flex h-9 w-9 items-center justify-center rounded text-aura-gray hover:text-aura-gray-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive">
                        <svg data-icon="eye" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12S5.25 5.25 12 5.25 21.75 12 21.75 12 18.75 18.75 12 18.75 2.25 12 2.25 12Z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <svg data-icon="eye-off" class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.58 10.58a2 2 0 0 0 2.83 2.83M9.88 5.4A9.4 9.4 0 0 1 12 5.25c6.75 0 9.75 6.75 9.75 6.75a16.5 16.5 0 0 1-3.1 3.85M6.35 6.35A16.5 16.5 0 0 0 2.25 12S5.25 18.75 12 18.75c1.25 0 2.4-.23 3.45-.62"/>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p id="password-error" role="alert" class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center gap-2 text-aura-gray">
                    <input type="checkbox" name="remember"
                           class="h-4 w-4 rounded border-aura-gray-light accent-aura-olive focus-visible:ring-2 focus-visible:ring-aura-olive">
                    Recordarme
                </label>

                <a href="{{ route('password.request') }}"
                   class="rounded text-aura-olive hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive">
                    ¿Olvidaste tu contraseña?
                </a>
            </div>

            <button type="submit"
                    class="w-full rounded bg-aura-olive py-2 text-sm font-medium text-white transition-opacity hover:opacity-90 focus:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive focus-visible:ring-offset-2 motion-reduce:transition-none">
                Entrar
            </button>
        </form>
    </div>

    <script>
        (function () {
            const toggle = document.getElementById('toggle-password');
            const field = document.getElementById('password');
            if (!toggle || !field) return;

            const eye = toggle.querySelector('[data-icon="eye"]');
            const eyeOff = toggle.querySelector('[data-icon="eye-off"]');

            toggle.addEventListener('click', function () {
                const reveal = field.type === 'password';
                field.type = reveal ? 'text' : 'password';
                toggle.setAttribute('aria-pressed', String(reveal));
                toggle.setAttribute('aria-label', reveal ? 'Ocultar contraseña' : 'Mostrar contraseña');
                eye.classList.toggle('hidden', reveal);
                eyeOff.classList.toggle('hidden', !reveal);
            });
        })();
    </script>
</x-guest-layout>
