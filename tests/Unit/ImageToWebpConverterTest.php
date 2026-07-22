<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\ImageToWebpConverter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ImageToWebpConverterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_max_pixels_constant_is_12_megapixels(): void
    {
        $this->assertSame(12_000_000, ImageToWebpConverter::MAX_PIXELS);
    }

    public function test_normal_small_jpeg_converts_and_stores_to_fake_public_disk(): void
    {
        $jpegContent = $this->generateJpeg(100, 100);
        $file = UploadedFile::fake()->createWithContent('photo.jpg', $jpegContent);

        $path = ImageToWebpConverter::storeUploadedFile(
            file: $file,
            directory: 'test-dir',
            errorField: 'image',
        );

        $disk = Storage::disk('public');

        $this->assertStringEndsWith('.webp', $path);
        $this->assertTrue($disk->exists($path));

        // Verify stored content is valid WebP
        $storedContent = $disk->get($path);
        $this->assertNotFalse($storedContent);
        $this->assertStringStartsWith('RIFF', $storedContent, 'Stored file should start with RIFF header (WebP)');
    }

    public function test_over_12mp_jpeg_is_rejected_before_gd_decode(): void
    {
        // Inflate SOF dimensions so getimagesize reports 5712×4284 (≈24.5 MP)
        // — the exact dimensions that caused the production OOM incident.
        $jpegContent = $this->generateJpeg(100, 100);
        $jpegContent = $this->patchJpegSofDimensions($jpegContent, 5712, 4284);

        $file = UploadedFile::fake()->createWithContent('huge.jpg', $jpegContent);

        try {
            ImageToWebpConverter::storeUploadedFile(
                file: $file,
                directory: 'test-dir',
                errorField: 'photo_field',
            );

            $this->fail('Expected ValidationException was not thrown for >12 MP JPEG.');
        } catch (ValidationException $e) {
            $messages = $e->errors();

            $this->assertArrayHasKey('photo_field', $messages);
            $errorText = implode(' ', $messages['photo_field']);

            $this->assertStringContainsString('5712', $errorText);
            $this->assertStringContainsString('4284', $errorText);
            $this->assertStringContainsString('24.5', $errorText);
            $this->assertStringContainsString('12 MP', $errorText);
        }

        $this->assertCount(0, Storage::disk('public')->allFiles('test-dir'));
    }

    public function test_unreadable_binary_is_rejected(): void
    {
        $file = UploadedFile::fake()->createWithContent('corrupt.jpg', 'not-a-real-image');

        try {
            ImageToWebpConverter::storeUploadedFile(
                file: $file,
                directory: 'test-dir',
                errorField: 'img',
            );

            $this->fail('Expected ValidationException for unreadable binary.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('img', $e->errors());
        }

        $this->assertCount(0, Storage::disk('public')->allFiles('test-dir'));
    }

    private function generateJpeg(int $width, int $height): string
    {
        $img = imagecreatetruecolor($width, $height);
        $grey = imagecolorallocate($img, 128, 128, 128);
        imagefilledrectangle($img, 0, 0, $width - 1, $height - 1, $grey);

        ob_start();
        imagejpeg($img, null, 80);
        $data = (string) ob_get_clean();
        imagedestroy($img);

        return $data;
    }

    /**
     * Patch SOF0 height/width bytes in a JPEG to make getimagesize report
     * arbitrary dimensions without actually allocating a large bitmap.
     */
    private function patchJpegSofDimensions(string $jpeg, int $newHeight, int $newWidth): string
    {
        $sof0Pos = strpos($jpeg, "\xFF\xC0");

        if ($sof0Pos === false) {
            $this->markTestSkipped('JPEG has no SOF0 marker — cannot patch dimensions.');
        }

        $jpeg[$sof0Pos + 5] = chr(($newHeight >> 8) & 0xFF);
        $jpeg[$sof0Pos + 6] = chr($newHeight & 0xFF);
        $jpeg[$sof0Pos + 7] = chr(($newWidth >> 8) & 0xFF);
        $jpeg[$sof0Pos + 8] = chr($newWidth & 0xFF);

        return $jpeg;
    }
}
