<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Aura Dental Club' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-aura-cream text-aura-gray-dark font-sans antialiased min-h-screen">
    <div class="flex min-h-screen">
        <aside class="w-64 shrink-0 border-r border-aura-gray-light bg-white px-6 py-8 hidden md:flex md:flex-col md:justify-between">
            <div>
                <div class="mb-10">
                    <span class="text-2xl font-light tracking-tight lowercase">aura</span>
                    <p class="text-[10px] uppercase tracking-widest text-aura-gray mt-1">dental club</p>
                </div>

                <nav class="space-y-1 text-sm">
                    <a href="{{ route('dashboard') }}"
                       class="block rounded px-3 py-2 {{ request()->routeIs('dashboard') ? 'bg-aura-olive text-white' : 'text-aura-gray-dark hover:bg-aura-cream' }}">
                        Inicio
                    </a>

                    @if (auth()->user()?->isSuperadmin())
                        <a href="#" class="block rounded px-3 py-2 text-aura-sage cursor-not-allowed" aria-disabled="true">
                            Configuración del sistema
                        </a>
                    @endif
                </nav>
            </div>

            <div class="text-sm border-t border-aura-gray-light pt-4">
                <p class="font-medium">{{ auth()->user()?->name }}</p>
                <p class="text-aura-gray text-xs">{{ auth()->user()?->role?->label() }}</p>

                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="text-aura-gray hover:text-aura-gray-dark text-xs underline">
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </aside>

        <main class="flex-1 px-6 py-8 md:px-12 md:py-12">
            {{ $slot }}
        </main>
    </div>
</body>
</html>
