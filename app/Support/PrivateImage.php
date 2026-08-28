<?php

namespace App\Support;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Guarda imágenes subidas por el usuario en el disco privado `local` (fuera de `public/`,
 * CLAUDE.md §5). La imagen NO se guarda tal cual: se vuelve a codificar con GD a JPEG, lo
 * que descarta todos los metadatos EXIF (coordenadas GPS, fecha, número de serie del
 * equipo — minimización de datos) y neutraliza cargas políglotas (imagen válida + payload
 * HTML/JS embebido).
 *
 * Base compartida por App\Support\PatientPhoto (foto de identificación del paciente) y
 * App\Support\UserAvatar (foto de perfil del personal).
 */
final class PrivateImage
{
    public const DISK = 'local';

    /** Lado máximo del JPEG resultante; una foto de identificación no necesita más. */
    private const MAX_SIDE = 1200;

    /** Lado máximo aceptado en la imagen de entrada — rechaza bombas de dimensiones antes de GD. */
    private const MAX_INPUT_SIDE = 12000;

    private const JPEG_QUALITY = 82;

    public static function store(
        UploadedFile $file,
        string $directory,
        string $errorField = 'photo',
        string $errorMessage = 'La imagen no se pudo procesar. Sube otro archivo.',
    ): string {
        $binary = self::reencodeToJpeg($file, $errorField, $errorMessage);

        $path = trim($directory, '/').'/'.Str::random(40).'.jpg';
        Storage::disk(self::DISK)->put($path, $binary);

        return $path;
    }

    public static function delete(?string $path): void
    {
        if ($path !== null && $path !== '') {
            Storage::disk(self::DISK)->delete($path);
        }
    }

    public static function exists(?string $path): bool
    {
        return $path !== null && $path !== '' && Storage::disk(self::DISK)->exists($path);
    }

    private static function reencodeToJpeg(UploadedFile $file, string $errorField, string $errorMessage): string
    {
        $image = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));

        if (! $image instanceof GdImage) {
            throw ValidationException::withMessages([$errorField => $errorMessage]);
        }

        if (imagesx($image) > self::MAX_INPUT_SIDE || imagesy($image) > self::MAX_INPUT_SIDE) {
            imagedestroy($image);
            throw ValidationException::withMessages([$errorField => $errorMessage]);
        }

        if ($file->getMimeType() === 'image/jpeg') {
            $image = self::applyExifOrientation($image, $file->getRealPath());
        }

        $image = self::flattenOntoWhite($image);
        $image = self::downscale($image);

        ob_start();
        imagejpeg($image, null, self::JPEG_QUALITY);
        $binary = (string) ob_get_clean();

        imagedestroy($image);

        return $binary;
    }

    private static function applyExifOrientation(GdImage $image, string $path): GdImage
    {
        $orientation = @exif_read_data($path)['Orientation'] ?? null;

        $rotated = match ($orientation) {
            3, 4 => imagerotate($image, 180, 0),
            5, 6 => imagerotate($image, -90, 0),
            7, 8 => imagerotate($image, 90, 0),
            default => $image,
        };

        if ($rotated instanceof GdImage && $rotated !== $image) {
            imagedestroy($image);
            $image = $rotated;
        }

        if (in_array($orientation, [2, 5, 7], true)) {
            imageflip($image, IMG_FLIP_HORIZONTAL);
        }

        if ($orientation === 4) {
            imageflip($image, IMG_FLIP_HORIZONTAL);
        }

        return $image;
    }

    private static function flattenOntoWhite(GdImage $image): GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);

        $canvas = imagecreatetruecolor($width, $height);
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
        imagecopy($canvas, $image, 0, 0, 0, 0, $width, $height);

        imagedestroy($image);

        return $canvas;
    }

    private static function downscale(GdImage $image): GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $longest = max($width, $height);

        if ($longest <= self::MAX_SIDE) {
            return $image;
        }

        $scale = self::MAX_SIDE / $longest;
        $resized = imagescale($image, (int) round($width * $scale), (int) round($height * $scale));

        if ($resized instanceof GdImage) {
            imagedestroy($image);

            return $resized;
        }

        return $image;
    }
}
