<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Aura Dental Club' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-aura-cream text-aura-gray-dark font-sans antialiased min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-sm">
        <div class="text-center mb-10">
            <span class="text-3xl font-light tracking-tight lowercase">aura</span>
            <p class="text-xs uppercase tracking-widest text-aura-gray mt-1">dental club</p>
        </div>

        <div class="bg-white border border-aura-gray-light rounded-lg p-8">
            {{ $slot }}
        </div>
    </div>
</body>
</html>
