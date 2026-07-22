<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Imagick;

class ImageToWebpConverter
{
    /**
     * Maximum megapixels for a single image. Exceeding this would risk a GD out-of-memory
     * fatal during imagecreatefromjpeg/png/webp.
     */
    public const MAX_PIXELS = 12_000_000;

    /**
     * MIME types that getimagesize can reliably read dimensions from.
     *
     * @var array<string>
     */
    private const GD_MIME_TYPES = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];

    /**
     * @param  array<string, string>  $messages
     */
    public static function storeUploadedFile(
        UploadedFile $file,
        string $directory,
        string $errorField,
        int $quality = 70,
        array $messages = [],
    ): string {
        $sourcePath = $file->getRealPath();

        if (! is_string($sourcePath) || $sourcePath === '') {
            throw ValidationException::withMessages([
                $errorField => $messages['invalid'] ?? 'Nepodařilo se načíst nahraný obrázek.',
            ]);
        }

        $mimeType = $file->getMimeType();
        $filename = Str::uuid().'.webp';
        $tempPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.$filename;

        try {
            if (self::shouldUseImagick($mimeType)) {
                self::convertWithImagick($sourcePath, $tempPath, $quality, $errorField, $messages);
            } else {
                self::convertWithGd($sourcePath, $mimeType, $tempPath, $quality, $errorField, $messages);
            }

            $disk = Storage::disk('public');

            if (! $disk->exists($directory)) {
                $disk->makeDirectory($directory);
            }

            $disk->putFileAs($directory, new File($tempPath), $filename, 'public');

            return $directory.'/'.$filename;
        } finally {
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        }
    }

    private static function shouldUseImagick(?string $mimeType): bool
    {
        return in_array($mimeType, ['image/heic', 'image/heif', 'image/heic-sequence', 'image/heif-sequence'], true);
    }

    /**
     * @param  array<string, string>  $messages
     */
    private static function convertWithImagick(
        string $sourcePath,
        string $targetPath,
        int $quality,
        string $errorField,
        array $messages,
    ): void {
        if (! extension_loaded('imagick') || ! class_exists(Imagick::class)) {
            throw ValidationException::withMessages([
                $errorField => $messages['heicUnsupported'] ?? 'Server nepodporuje převod HEIC/HEIF obrázků. Nahrajte prosím JPG, PNG nebo WEBP.',
            ]);
        }

        // Lightweight dimension preflight via pingImage (first frame only for
        // multi-frame HEIC sequences). Both the preflight and the full conversion
        // share a single catch-block so that any unexpected Imagick exception
        // becomes a field-mapped ValidationException.
        /** @var Imagick|null $ping */
        $ping = null;
        /** @var Imagick|null $image */
        $image = null;

        try {
            $ping = new Imagick;
            $ping->pingImage($sourcePath);
            self::assertDimensions(
                $ping->getImageWidth(),
                $ping->getImageHeight(),
                $errorField,
                $messages,
            );

            $image = new Imagick($sourcePath);
            $image->setImageOrientation(Imagick::ORIENTATION_TOPLEFT);
            $image->setImageFormat('webp');
            $image->setImageCompressionQuality($quality);

            if (! $image->writeImage($targetPath)) {
                throw ValidationException::withMessages([
                    $errorField => $messages['encodeFailed'] ?? 'Nepodařilo se uložit obrázek do formátu WebP.',
                ]);
            }
        } catch (ValidationException $ve) {
            throw $ve;
        } catch (\Throwable $te) {
            throw ValidationException::withMessages([
                $errorField => $messages['heicFailed'] ?? 'Formát HEIC/HEIF se na serveru nepodařilo převést. Nahrajte prosím JPG, PNG nebo WEBP.',
            ]);
        } finally {
            if ($ping !== null) {
                $ping->clear();
                $ping->destroy();
            }

            if ($image !== null) {
                $image->clear();
                $image->destroy();
            }
        }
    }

    /**
     * Throw a ValidationException when width × height exceeds MAX_PIXELS.
     *
     * @param  array<string, string>  $messages
     */
    private static function assertDimensions(
        int $width,
        int $height,
        string $errorField,
        array $messages,
    ): void {
        if ($width <= 0 || $height <= 0) {
            throw ValidationException::withMessages([
                $errorField => $messages['unreadable'] ?? 'Obrázek má neplatné rozměry.',
            ]);
        }

        $pixels = $width * $height;

        if ($pixels > self::MAX_PIXELS) {
            throw ValidationException::withMessages([
                $errorField => $messages['tooLarge'] ?? sprintf(
                    'Obrázek má příliš vysoké rozlišení (%d × %d px, tj. %.1f MP). Maximum je 12 MP. Nahrajte prosím menší obrázek.',
                    $width,
                    $height,
                    $pixels / 1_000_000,
                ),
            ]);
        }
    }

    /**
     * Read dimensions via getimagesize for GD-compatible formats and reject
     * images that exceed MAX_PIXELS before a GD decode that would OOM.
     *
     * @param  array<string, string>  $messages
     */
    private static function preflightDimensions(
        string $sourcePath,
        ?string $mimeType,
        string $errorField,
        array $messages,
    ): void {
        if ($mimeType === null || ! in_array($mimeType, self::GD_MIME_TYPES, true)) {
            return;
        }

        $size = @getimagesize($sourcePath);

        if ($size === false) {
            throw ValidationException::withMessages([
                $errorField => $messages['unreadable'] ?? 'Obrázek se nepodařilo přečíst – soubor je poškozený nebo má nepodporovaný formát.',
            ]);
        }

        self::assertDimensions((int) $size[0], (int) $size[1], $errorField, $messages);
    }

    /**
     * @param  array<string, string>  $messages
     */
    private static function convertWithGd(
        string $sourcePath,
        ?string $mimeType,
        string $targetPath,
        int $quality,
        string $errorField,
        array $messages,
    ): void {
        self::ensureWebPSupport($errorField, $messages);
        self::preflightDimensions($sourcePath, $mimeType, $errorField, $messages);

        /** @var \GdImage|resource|null $image */
        $image = null;

        try {
            $image = match ($mimeType) {
                'image/jpeg', 'image/jpg' => self::createImageFromJpeg($sourcePath, $errorField),
                'image/png' => self::createImageFromPng($sourcePath, $errorField),
                'image/webp' => self::createImageFromWebP($sourcePath, $errorField),
                default => throw ValidationException::withMessages([
                    $errorField => ($messages['unsupported'] ?? 'Nepodporovaný formát obrázku: ').($mimeType ?? 'neznámý'),
                ]),
            };

            if (! imagewebp($image, $targetPath, $quality)) {
                throw ValidationException::withMessages([
                    $errorField => $messages['encodeFailed'] ?? 'Nepodařilo se uložit obrázek do formátu WebP.',
                ]);
            }
        } finally {
            if ($image !== null) {
                imagedestroy($image);
            }
        }
    }

    /**
     * @param  array<string, string>  $messages
     */
    private static function ensureWebPSupport(string $errorField, array $messages): void
    {
        if (! (imagetypes() & IMG_WEBP)) {
            throw ValidationException::withMessages([
                $errorField => $messages['webpUnsupported'] ?? 'Server nepodporuje WebP konverzi. Kontaktujte správce systému.',
            ]);
        }
    }

    /**
     * @return \GdImage|resource
     */
    private static function createImageFromJpeg(string $path, string $errorField): mixed
    {
        $image = imagecreatefromjpeg($path);

        if ($image === false) {
            throw ValidationException::withMessages([
                $errorField => 'Nepodařilo se načíst JPEG obrázek.',
            ]);
        }

        return $image;
    }

    /**
     * @return \GdImage|resource
     */
    private static function createImageFromPng(string $path, string $errorField): mixed
    {
        $image = imagecreatefrompng($path);

        if ($image === false) {
            throw ValidationException::withMessages([
                $errorField => 'Nepodařilo se načíst PNG obrázek.',
            ]);
        }

        imagepalettetotruecolor($image);
        imagesavealpha($image, true);

        return $image;
    }

    /**
     * @return \GdImage|resource
     */
    private static function createImageFromWebP(string $path, string $errorField): mixed
    {
        $image = imagecreatefromwebp($path);

        if ($image === false) {
            throw ValidationException::withMessages([
                $errorField => 'Nepodařilo se načíst WebP obrázek.',
            ]);
        }

        imagepalettetotruecolor($image);
        imagesavealpha($image, true);

        return $image;
    }
}
