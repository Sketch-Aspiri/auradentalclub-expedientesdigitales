<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

/**
 * Foto de perfil del personal de la clínica. Fachada sobre App\Support\PrivateImage:
 * disco privado `local`, servida por la ruta autorizada `profile.photo`. Solo la propia
 * cuenta ve su avatar; no se muestra el de otros usuarios.
 */
final class UserAvatar
{
    public const DISK = PrivateImage::DISK;

    public const DIRECTORY = 'user-avatars';

    public static function store(UploadedFile $file): string
    {
        return PrivateImage::store($file, self::DIRECTORY, 'photo');
    }

    public static function delete(?string $path): void
    {
        PrivateImage::delete($path);
    }

    public static function exists(?string $path): bool
    {
        return PrivateImage::exists($path);
    }
}
