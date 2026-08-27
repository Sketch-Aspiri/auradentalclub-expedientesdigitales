@php
    $user = auth()->user();
@endphp

<div class="relative" data-user-dropdown>
    <button type="button" data-user-dropdown-toggle aria-expanded="false" aria-haspopup="true"
            class="flex items-center gap-3 rounded-full py-1 pl-3 pr-1 text-left transition-colors hover:bg-aura-cream focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive">
        <span class="leading-tight">
            <span class="block text-sm font-medium text-aura-gray-dark">{{ $user?->name }}</span>
            <span class="block text-xs text-aura-gray">{{ $user?->role?->label() }}</span>
        </span>
        <x-user-avatar size="sm" />
    </button>

    <div data-user-dropdown-panel
         class="absolute right-0 z-40 mt-2 hidden w-52 overflow-hidden rounded-lg border border-aura-gray-light bg-white py-1 shadow-lg">
        <a href="{{ route('profile.edit') }}"
           class="flex items-center gap-2 px-3 py-2 text-sm text-aura-gray-dark transition-colors hover:bg-aura-cream hover:text-aura-olive focus-visible:outline-none focus-visible:bg-aura-cream">
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 19v-1a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v1M12 11a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z"/>
            </svg>
            Mi perfil
        </a>

        <form method="POST" action="{{ route('logout') }}" class="border-t border-aura-gray-light">
            @csrf
            <button type="submit"
                    class="flex w-full items-center gap-2 px-3 py-2 text-sm text-aura-gray-dark transition-colors hover:bg-aura-cream hover:text-aura-olive focus-visible:outline-none focus-visible:bg-aura-cream">
                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H3.75m0 0 3.5-3.5M3.75 12l3.5 3.5M10 7.5V6a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-6a2 2 0 0 1-2-2v-1.5"/>
                </svg>
                Cerrar sesión
            </button>
        </form>
    </div>
</div>

@once
    <script>
        document.addEventListener('click', function (e) {
                document.querySelectorAll('[data-user-dropdown]').forEach(function (root) {
                    const toggle = root.querySelector('[data-user-dropdown-toggle]');
                    const panel = root.querySelector('[data-user-dropdown-panel]');
                    if (!toggle || !panel) return;

                    if (toggle.contains(e.target)) {
                        const open = panel.classList.toggle('hidden');
                        toggle.setAttribute('aria-expanded', String(!open));
                    } else if (!panel.contains(e.target)) {
                        panel.classList.add('hidden');
                        toggle.setAttribute('aria-expanded', 'false');
                    }
                });
            });

            document.addEventListener('keydown', function (e) {
                if (e.key !== 'Escape') return;
                document.querySelectorAll('[data-user-dropdown-panel]:not(.hidden)').forEach(function (panel) {
                    panel.classList.add('hidden');
                    const toggle = panel.closest('[data-user-dropdown]')?.querySelector('[data-user-dropdown-toggle]');
                    toggle?.setAttribute('aria-expanded', 'false');
                    toggle?.focus();
                });
            });
    </script>
@endonce
