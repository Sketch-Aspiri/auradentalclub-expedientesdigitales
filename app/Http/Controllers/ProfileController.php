<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Support\UserAvatar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Perfil de la cuenta autenticada. Solo se edita la propia cuenta (no hay id en las rutas),
 * y solo nombre / correo / contraseña — el rol se gestiona aparte (CLAUDE.md §3).
 */
class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('profile.edit', ['user' => Auth::user()]);
    }

    /**
     * Sirve la foto de perfil de la cuenta autenticada desde el disco privado `local`.
     * Solo la propia foto (no hay id en la ruta); nunca una URL directa al archivo (§5).
     */
    public function photo(): BinaryFileResponse
    {
        $user = Auth::user();

        abort_unless(UserAvatar::exists($user->photo_path), 404);

        return response()->file(
            Storage::disk(UserAvatar::DISK)->path($user->photo_path),
            [
                'Cache-Control' => 'private, no-store',
                'X-Content-Type-Options' => 'nosniff',
                'Content-Disposition' => 'inline',
            ],
        );
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $previousPhoto = $user->photo_path;

        $user->fill($request->safe()->only(['name', 'email']));

        if ($photo = $request->file('photo')) {
            $user->photo_path = UserAvatar::store($photo);
        } elseif ($request->boolean('remove_photo')) {
            $user->photo_path = null;
        }

        $user->save();

        if ($previousPhoto !== null && $previousPhoto !== $user->photo_path) {
            UserAvatar::delete($previousPhoto);
        }

        return redirect()->route('profile.edit')
            ->with('status', 'Perfil actualizado correctamente.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => $request->validated('password'),
        ]);

        // Rota el id de sesión tras cambiar la contraseña (mitiga fijación de sesión).
        $request->session()->regenerate();

        return redirect()->route('profile.edit')
            ->with('status', 'Contraseña actualizada correctamente.');
    }
}
