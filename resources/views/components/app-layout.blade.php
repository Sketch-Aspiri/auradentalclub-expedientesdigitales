<!DOCTYPE html>
<html lang="es" class="scroll-pt-16 md:scroll-pt-20">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Aura Dental Club' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('logos/monograma.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('logos/monograma.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Fuerza la carga de Alpine.js (viene empaquetado con Livewire) en toda vista de este
         shell, incluso en páginas sin un componente Livewire — Livewire solo inyecta sus
         assets automáticamente si detecta un componente renderizado en la misma petición. --}}
    @livewireStyles
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
        {{-- Barra lateral de escritorio: alto fijo al viewport y anclada en su lugar (sticky),
             para que con scroll 0 siempre se vea completa (logo, navegación y bloque de usuario
             al pie), sin importar qué tan larga sea la página. Si la navegación llegara a superar
             el alto disponible, solo esa zona hace scroll interno; el bloque de usuario nunca se
             mueve. Se eligió `sticky` (no `fixed`) porque sigue participando en el flujo normal
             del `md:flex` existente: no requiere reservar margen/padding manual en la columna de
             contenido ni recalcular anchos, así que es el cambio menos invasivo sobre el layout
             actual. `self-start` evita que el flex `stretch` por defecto infle su altura. --}}
        <aside class="hidden md:sticky md:top-0 md:flex md:h-screen md:w-64 md:shrink-0 md:flex-col md:self-start md:border-r md:border-aura-gray-light md:bg-white">
            <div class="min-h-0 flex-1 overflow-y-auto px-6 pt-8">
                <a href="{{ route('dashboard') }}"
                   class="mb-10 block w-fit rounded focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive">
                    <img src="{{ asset('logos/logo_completo.png') }}" alt="aura dental club"
                         width="3604" height="1394" class="h-12 w-auto">
                </a>

                <nav aria-label="Principal">
                    <x-nav-links />
                </nav>
            </div>

            <div class="shrink-0 border-t border-aura-gray-light px-6 py-4">
                <x-user-menu :identity="false" />
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            {{-- Barra superior de escritorio: fija al hacer scroll (sticky, fondo sólido para que
                 el contenido no se transparente por debajo). z-20: por encima del contenido de
                 `<main>`, pero por debajo del panel del menú de usuario (z-40, definido en
                 `x-user-dropdown`) y de cualquier modal de confirmación (usar z-50+). No compite
                 con el encabezado móvil (z-30): son mutuamente excluyentes vía `md:block`/`md:hidden`. --}}
            {{-- Esquema de z-index del shell (de menor a mayor): header sticky de escritorio
                 z-20 → header móvil z-30 → panel del menú de usuario (x-user-dropdown) z-40 →
                 overlay de navegación / modales de confirmación z-50+. Cada capa nueva debe
                 quedar documentada aquí para no pisar a las anteriores por accidente. --}}
            <header class="hidden border-b border-aura-gray-light bg-white px-6 py-3 md:sticky md:top-0 md:z-20 md:block md:px-12">
                <div class="mx-auto flex max-w-5xl items-center justify-end">
                    <x-user-dropdown />
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

    {{--
        Overlay de navegación (isotipo de Aura) — mecanismo de feedback de carga #1
        (CLAUDE.md §10 / .claude/agents/ux-ui-designer.md "Feedback de carga"). La lógica
        vive en resources/js/nav-loader.js (cargado vía app.js): retardo de ~200ms antes
        de mostrarse, exclusiones (anclas, target=_blank, mailto/tel, download,
        clic con modificadores, wire:submit), reset en `pageshow` (bfcache) y timeout de
        seguridad de 10s para que nunca quede colgado.

        z-50: por encima del header móvil (z-30), el header sticky de escritorio (z-20) y
        el panel del menú de usuario (z-40) — ver el esquema de z-index comentado arriba,
        junto al header de escritorio.

        `pointer-events-none`: el overlay nunca atrapa clics ni el foco del teclado (si el
        timeout de seguridad falla por lo que sea, la app sigue siendo usable debajo).
        `role="status"`/`aria-live="polite"` + texto `sr-only`: se anuncia a lectores de
        pantalla sin ser un diálogo modal. El isotipo usa una animación de opacidad sutil
        (`animate-pulse`), nunca un spinner genérico girando; con `prefers-reduced-motion`
        la regla global de app.css ya congela cualquier animación/transición de esta capa.
    --}}
    <div
        id="nav-loading-overlay"
        class="pointer-events-none invisible fixed inset-0 z-50 flex flex-col items-center justify-center gap-5 bg-aura-cream/95 opacity-0 transition-opacity duration-200 motion-reduce:transition-none"
        role="status"
        aria-live="polite"
    >
        {{-- El centrado depende de que `flex` esté SIEMPRE presente: se oculta con
             `invisible` (visibility), no con `hidden` (display:none), porque alternar
             display anula el centrado del flex y el isotipo se iría a la esquina. --}}
        <img src="{{ asset('logos/monograma.png') }}" alt="" aria-hidden="true" width="2502" height="2466"
             class="h-14 w-14">

        {{-- Puntos de carga: el movimiento vive aquí, no en el isotipo (una marca que
             late compite con los puntos y ensucia la pantalla). `aura-dot` se congela
             sola con la regla global de `prefers-reduced-motion` de app.css. --}}
        <div class="flex items-center gap-1.5" aria-hidden="true">
            <span class="aura-dot h-1.5 w-1.5 rounded-full bg-aura-olive"></span>
            <span class="aura-dot h-1.5 w-1.5 rounded-full bg-aura-olive"></span>
            <span class="aura-dot h-1.5 w-1.5 rounded-full bg-aura-olive"></span>
        </div>

        <span class="sr-only">Cargando…</span>
    </div>

    @livewireScripts
</body>
</html>
