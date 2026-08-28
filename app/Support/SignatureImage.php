<?php

namespace App\Support;

use GdImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Guarda las firmas dibujadas en el canvas (signature_pad) en el disco privado `local` (fuera
 * de `public/`, CLAUDE.md §5). El dataURL PNG NO se guarda tal cual: se decodifica y se vuelve a
 * codificar con GD, lo que descarta cualquier metadato y neutraliza cargas políglotas (imagen
 * válida + payload embebido). Misma filosofía que App\Support\PrivateImage, adaptada al dataURL.
 */
final class SignatureImage
{
    public const DISK = 'local';

    /** Lado máximo del PNG resultante; una firma no necesita más resolución. */
    private const MAX_SIDE = 1000;

    /**
     * Lado máximo aceptado en el PNG de entrada. Un canvas de firma real no pasa de ~1500 px;
     * rechazar por encima evita una bomba de dimensiones (un PNG pequeño comprimido que expande
     * a gigabytes al crear el lienzo truecolor).
     */
    private const MAX_INPUT_SIDE = 4000;

    private const ERROR_MESSAGE = 'La firma no se pudo procesar. Vuelve a firmar en el recuadro.';

    /**
     * @param  string  $dataUrl  "data:image/png;base64,...." tal como lo entrega canvas.toDataURL()
     */
    public static function store(string $dataUrl, string $directory, string $errorField = 'signature'): string
    {
        $binary = self::decodeAndReencode($dataUrl, $errorField);

        $path = trim($directory, '/').'/'.Str::random(40).'.png';
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

    /**
     * Comprueba que el string tiene la forma de un dataURL PNG de canvas. Se usa como regla de
     * validación en el componente de firma antes de intentar persistir.
     */
    public static function looksLikePngDataUrl(?string $value): bool
    {
        return is_string($value) && str_starts_with($value, 'data:image/png;base64,');
    }

    private static function decodeAndReencode(string $dataUrl, string $errorField): string
    {
        if (! self::looksLikePngDataUrl($dataUrl)) {
            throw ValidationException::withMessages([$errorField => self::ERROR_MESSAGE]);
        }

        $base64 = substr($dataUrl, strlen('data:image/png;base64,'));
        $raw = base64_decode($base64, true);

        if ($raw === false) {
            throw ValidationException::withMessages([$errorField => self::ERROR_MESSAGE]);
        }

        $image = @imagecreatefromstring($raw);

        if (! $image instanceof GdImage) {
            throw ValidationException::withMessages([$errorField => self::ERROR_MESSAGE]);
        }

        if (imagesx($image) > self::MAX_INPUT_SIDE || imagesy($image) > self::MAX_INPUT_SIDE) {
            imagedestroy($image);
            throw ValidationException::withMessages([$errorField => self::ERROR_MESSAGE]);
        }

        $image = self::flattenOntoWhite($image);
        $image = self::downscale($image);

        ob_start();
        imagepng($image, null, 6);
        $binary = (string) ob_get_clean();

        imagedestroy($image);

        return $binary;
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
        $longest = max(imagesx($image), imagesy($image));

        if ($longest <= self::MAX_SIDE) {
            return $image;
        }

        $scale = self::MAX_SIDE / $longest;
        $resized = imagescale($image, (int) round(imagesx($image) * $scale), (int) round(imagesy($image) * $scale));

        if ($resized instanceof GdImage) {
            imagedestroy($image);

            return $resized;
        }

        return $image;
    }
}
