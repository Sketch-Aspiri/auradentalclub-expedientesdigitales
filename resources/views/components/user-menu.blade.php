<div class="text-sm">
    <p class="font-medium text-aura-gray-dark">{{ auth()->user()?->name }}</p>
    <p class="mt-0.5 text-xs text-aura-gray">{{ auth()->user()?->role?->label() }}</p>

    <form method="POST" action="{{ route('logout') }}" class="mt-2">
        @csrf
        <button type="submit"
                class="-mx-3 inline-flex items-center gap-2 rounded px-3 py-2 text-sm text-aura-gray-dark transition-colors motion-reduce:transition-none hover:bg-aura-cream hover:text-aura-olive focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive">
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H3.75m0 0 3.5-3.5M3.75 12l3.5 3.5M10 7.5V6a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-6a2 2 0 0 1-2-2v-1.5"/>
            </svg>
            Cerrar sesión
        </button>
    </form>
</div>
