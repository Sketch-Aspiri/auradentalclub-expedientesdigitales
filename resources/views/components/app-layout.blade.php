<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Aura Dental Club' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('logos/monograma.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('logos/monograma.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-aura-cream text-aura-gray-dark font-sans antialiased min-h-screen">
    {{-- Encabezado móvil con menú desplegable (sin navegación en escritorio) --}}
    <header class="sticky top-0 z-30 border-b border-aura-gray-light bg-white md:hidden">
        <div class="flex items-center justify-between px-4 py-3">
            <a href="{{ route('dashboard') }}"
               class="inline-flex rounded focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive">
                <img src="{{ asset('logos/logo_completo.png') }}" alt="aura dental club"
                     width="3604" height="1394" class="h-10 w-auto">
            </a>

            <button type="button" id="nav-toggle" aria-expanded="false" aria-controls="mobile-nav"
                    aria-label="Abrir menú"
                    class="inline-flex h-10 w-10 items-center justify-center rounded text-aura-gray-dark hover:bg-aura-cream focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive">
                <svg data-icon="open" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/>
                </svg>
                <svg data-icon="close" class="hidden h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" d="M6 6 18 18M18 6 6 18"/>
                </svg>
            </button>
        </div>

        <nav id="mobile-nav" aria-label="Principal" class="hidden border-t border-aura-gray-light px-3 py-3">
            <x-nav-links />
            <div class="mt-3 border-t border-aura-gray-light pt-3">
                <x-user-menu />
            </div>
        </nav>
    </header>

    <div class="md:flex md:min-h-screen">
        {{-- Barra lateral de escritorio --}}
        <aside class="hidden md:flex md:w-64 md:shrink-0 md:flex-col md:justify-between md:border-r md:border-aura-gray-light md:bg-white md:px-6 md:py-8">
            <div>
                <a href="{{ route('dashboard') }}"
                   class="mb-10 block w-fit rounded focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive">
                    <img src="{{ asset('logos/logo_completo.png') }}" alt="aura dental club"
                         width="3604" height="1394" class="h-12 w-auto">
                </a>

                <nav aria-label="Principal">
                    <x-nav-links />
                </nav>
            </div>

            <div class="border-t border-aura-gray-light pt-4">
                <x-user-menu />
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            {{-- Barra superior de escritorio: identidad de la cuenta --}}
            <header class="hidden border-b border-aura-gray-light bg-white px-6 py-3 md:block md:px-12">
                <div class="mx-auto flex max-w-5xl items-center justify-end gap-3" aria-hidden="true">
                    <div class="text-right leading-tight">
                        <p class="text-sm font-medium text-aura-gray-dark">{{ auth()->user()?->name }}</p>
                        <p class="text-xs text-aura-gray">{{ auth()->user()?->role?->label() }}</p>
                    </div>
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-aura-olive">
                        <img src="{{ asset('logos/monograma.png') }}" alt="" class="h-4 w-4 brightness-0 invert">
                    </span>
                </div>
            </header>

            <main class="flex-1 px-6 py-8 md:px-12 md:py-12">
                <div class="mx-auto max-w-5xl">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    <script>
        (function () {
            const btn = document.getElementById('nav-toggle');
            const nav = document.getElementById('mobile-nav');
            if (!btn || !nav) return;

            const openIcon = btn.querySelector('[data-icon="open"]');
            const closeIcon = btn.querySelector('[data-icon="close"]');

            const setOpen = function (open) {
                nav.classList.toggle('hidden', !open);
                btn.setAttribute('aria-expanded', String(open));
                btn.setAttribute('aria-label', open ? 'Cerrar menú' : 'Abrir menú');
                openIcon.classList.toggle('hidden', open);
                closeIcon.classList.toggle('hidden', !open);
            };

            btn.addEventListener('click', function () {
                setOpen(nav.classList.contains('hidden'));
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && !nav.classList.contains('hidden')) {
                    setOpen(false);
                    btn.focus();
                }
            });
        })();
    </script>
</body>
</html>
