<?php

namespace App\Support;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Almacenamiento de la foto de identificación del paciente. Siempre en el disco privado
 * `local` (fuera de `public/`), servida por la ruta autorizada `patients.photo` — CLAUDE.md §5.
 *
 * La imagen subida NO se guarda tal cual: se vuelve a codificar con GD a JPEG, lo que
 * descarta todos los metadatos EXIF (coordenadas GPS, fecha, número de serie del equipo —
 * minimización de datos, NOM-004 / LFPDPPP) y neutraliza cualquier carga políglota
 * (imagen válida + payload HTML/JS embebido).
 */
final class PatientPhoto
{
    public const DISK = 'local';

    public const DIRECTORY = 'patient-photos';

    /** Lado máximo del JPEG resultante; una foto de identificación no necesita más. */
    private const MAX_SIDE = 1200;

    private const JPEG_QUALITY = 82;

    public static function store(UploadedFile $file): string
    {
        $binary = self::reencodeToJpeg($file);

        $path = self::DIRECTORY.'/'.Str::random(40).'.jpg';
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

    private static function reencodeToJpeg(UploadedFile $file): string
    {
        $image = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));

        if (! $image instanceof GdImage) {
            throw ValidationException::withMessages([
                'photo' => 'La foto no se pudo procesar. Sube otra imagen.',
            ]);
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
