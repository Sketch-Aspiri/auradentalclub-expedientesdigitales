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
<body class="bg-aura-cream text-aura-gray-dark font-sans antialiased">
    @isset($aside)
        {{-- Layout a pantalla completa: panel de marca (oculto en móvil) + formulario centrado. --}}
        <div class="min-h-dvh bg-white md:grid md:grid-cols-2">
            <aside class="relative hidden overflow-hidden bg-aura-olive p-10 text-aura-cream md:flex md:flex-col md:justify-between lg:p-14">
                {{ $aside }}
            </aside>

            <div class="flex min-h-dvh flex-col justify-center px-6 py-12 sm:px-10 lg:px-16">
                {{ $slot }}
            </div>
        </div>
    @else
        <div class="flex min-h-dvh items-center justify-center px-4 py-10">
            <div class="w-full max-w-sm">
                <div class="mb-10 flex justify-center">
                    <img src="{{ asset('logos/logo_completo.png') }}" alt="aura dental club"
                         width="3604" height="1394" loading="eager"
                         class="h-10 w-auto">
                </div>

                <div class="bg-white border border-aura-gray-light rounded-lg p-8">
                    {{ $slot }}
                </div>
            </div>
        </div>
    @endisset
</body>
</html>
