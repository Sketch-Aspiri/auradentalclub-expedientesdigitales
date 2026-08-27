<x-app-layout title="Mi perfil">
    <div class="max-w-2xl">
        <div class="mb-6">
            <h1 class="text-lg font-medium text-aura-gray-dark">Mi perfil</h1>
            <p class="mt-1 text-sm text-aura-gray">
                Actualiza los datos de tu cuenta.
                Tu rol (<span class="font-medium text-aura-gray-dark">{{ $user->role->label() }}</span>)
                lo gestiona un administrador.
            </p>
        </div>

        @if (session('status'))
            <p role="status" class="mb-6 rounded-md border border-aura-olive/30 bg-aura-olive/5 px-4 py-2 text-sm text-aura-olive">
                {{ session('status') }}
            </p>
        @endif

        {{-- Datos de la cuenta --}}
        <section class="rounded-lg border border-aura-gray-light bg-white p-6">
            <h2 class="text-base font-medium text-aura-gray-dark">Datos de la cuenta</h2>

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf
                @method('PUT')

                {{-- Foto de perfil --}}
                <div>
                    <span class="mb-1 block text-sm text-aura-gray-dark">Foto de perfil</span>
                    <div class="flex items-center gap-4">
                        <span data-avatar-preview class="relative inline-flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-full bg-aura-olive text-base font-medium uppercase text-white">
                            <span>{{ $user->initials }}</span>
                            @if ($user->hasPhoto())
                                <img data-avatar-current src="{{ route('profile.photo') }}" alt=""
                                     class="absolute inset-0 h-full w-full object-cover" onerror="this.remove()">
                            @endif
                        </span>

                        <div class="text-sm">
                            <label for="photo"
                                   class="inline-flex cursor-pointer items-center rounded border border-aura-gray-light px-3 py-1.5 text-sm text-aura-gray-dark transition-colors hover:bg-aura-cream focus-within:ring-2 focus-within:ring-aura-olive">
                                Elegir imagen
                                <input id="photo" type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="sr-only">
                            </label>
                            <p class="mt-1 text-xs text-aura-gray">JPG, PNG o WebP · máx. 4 MB</p>

                            @if ($user->hasPhoto())
                                <label class="mt-2 flex items-center gap-2 text-xs text-aura-gray-dark">
                                    <input type="checkbox" name="remove_photo" value="1"
                                           class="h-4 w-4 rounded border-aura-gray-light accent-aura-olive">
                                    Quitar foto actual
                                </label>
                            @endif

                            @error('photo', 'updateProfile')
                                <p class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div>
                    <label for="name" class="mb-1 block text-sm text-aura-gray-dark">Nombre</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name"
                           class="w-full rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">
                    @error('name', 'updateProfile')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="mb-1 block text-sm text-aura-gray-dark">Correo electrónico</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="email"
                           class="w-full rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">
                    @error('email', 'updateProfile')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="profile_current_password" class="mb-1 block text-sm text-aura-gray-dark">
                        Contraseña actual
                        <span class="font-normal text-aura-gray">— solo necesaria si cambias el correo</span>
                    </label>
                    <input id="profile_current_password" type="password" name="current_password" autocomplete="current-password"
                           class="w-full rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">
                    @error('current_password', 'updateProfile')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="rounded bg-aura-olive px-4 py-2 text-sm font-medium text-white transition-opacity hover:opacity-90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive focus-visible:ring-offset-2">
                        Guardar datos
                    </button>
                </div>
            </form>
        </section>

        {{-- Contraseña --}}
        <section class="mt-6 rounded-lg border border-aura-gray-light bg-white p-6">
            <h2 class="text-base font-medium text-aura-gray-dark">Contraseña</h2>
            <p class="mt-1 text-sm text-aura-gray">Usa una contraseña larga y única para esta cuenta.</p>

            <form method="POST" action="{{ route('profile.password') }}" class="mt-4 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="mb-1 block text-sm text-aura-gray-dark">Contraseña actual</label>
                    <input id="current_password" type="password" name="current_password" required autocomplete="current-password"
                           class="w-full rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">
                    @error('current_password', 'updatePassword')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="mb-1 block text-sm text-aura-gray-dark">Nueva contraseña</label>
                    <input id="password" type="password" name="password" required autocomplete="new-password"
                           class="w-full rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">
                    @error('password', 'updatePassword')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="mb-1 block text-sm text-aura-gray-dark">Confirmar nueva contraseña</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                           class="w-full rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="rounded bg-aura-olive px-4 py-2 text-sm font-medium text-white transition-opacity hover:opacity-90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive focus-visible:ring-offset-2">
                        Cambiar contraseña
                    </button>
                </div>
            </form>
        </section>

        <a href="{{ route('dashboard') }}"
           class="mt-6 inline-block text-sm text-aura-gray hover:text-aura-gray-dark">
            &larr; Volver al inicio
        </a>
    </div>

    <script>
        (function () {
            const input = document.getElementById('photo');
            const preview = document.querySelector('[data-avatar-preview]');
            const remove = document.querySelector('input[name="remove_photo"]');
            if (!input || !preview) return;

            let objectUrl = null;

            input.addEventListener('change', function () {
                const file = input.files && input.files[0];
                if (objectUrl) { URL.revokeObjectURL(objectUrl); objectUrl = null; }
                if (!file) return;

                if (remove) { remove.checked = false; }

                objectUrl = URL.createObjectURL(file);
                let img = preview.querySelector('img');
                if (!img) {
                    img = document.createElement('img');
                    img.alt = '';
                    img.className = 'absolute inset-0 h-full w-full object-cover';
                    preview.appendChild(img);
                }
                img.src = objectUrl;
            });

            if (remove) {
                remove.addEventListener('change', function () {
                    if (!remove.checked) return;
                    input.value = '';
                    if (objectUrl) { URL.revokeObjectURL(objectUrl); objectUrl = null; }
                    const img = preview.querySelector('img');
                    if (img && !img.hasAttribute('data-avatar-current')) img.remove();
                    const current = preview.querySelector('[data-avatar-current]');
                    if (current) current.style.display = 'none';
                });
            }
        })();
    </script>
</x-app-layout>
