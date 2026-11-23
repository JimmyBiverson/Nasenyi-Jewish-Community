<?php

namespace Tests\Unit;

use App\Support\ImageUpload;
use PHPUnit\Framework\TestCase;

class ImageUploadTest extends TestCase
{
    public function test_inspect_accepts_every_supported_image_format(): void
    {
        $paths = [];

        try {
            foreach (['jpg', 'png', 'gif', 'webp', 'bmp'] as $extension) {
                if ($extension === 'webp' && ! function_exists('imagewebp')) {
                    continue;
                }

                $paths[$extension] = $this->createRasterImage($extension);
            }

            $paths['avif'] = $this->createAvifImage(32, 24);
            $paths['ico'] = $this->createIconImage(32, 24);

            foreach ($paths as $extension => $path) {
                $inspection = ImageUpload::inspect($path);

                $this->assertNotNull($inspection, $extension);
                $this->assertSame($extension, $inspection['extension']);
                $this->assertSame(32, $inspection['width']);
                $this->assertSame(24, $inspection['height']);
            }
        } finally {
            foreach ($paths as $path) {
                if (is_file($path)) {
                    unlink($path);
                }
            }
        }
    }

    public function test_inspect_rejects_files_that_are_not_real_images(): void
    {
        $path = $this->temporaryPath('html');
        file_put_contents($path, '<!doctype html><html>Not an image</html>');

        try {
            $this->assertNull(ImageUpload::inspect($path));
        } finally {
            unlink($path);
        }
    }

    public function test_extension_and_mime_helpers_ignore_query_strings(): void
    {
        $this->assertSame('jpg', ImageUpload::extension('/storage/content/logo.JPG?v=2'));
        $this->assertSame('image/jpeg', ImageUpload::mime('/storage/content/logo.JPG?v=2'));
        $this->assertSame('image/x-icon', ImageUpload::mime('https://example.org/favicon.ico'));
        $this->assertNull(ImageUpload::extension('/storage/content/no-extension'));
    }

    private function createRasterImage(string $extension): string
    {
        $path = $this->temporaryPath($extension);
        $image = imagecreatetruecolor(32, 24);

        match ($extension) {
            'jpg' => imagejpeg($image, $path),
            'png' => imagepng($image, $path),
            'gif' => imagegif($image, $path),
            'webp' => imagewebp($image, $path),
            'bmp' => imagebmp($image, $path),
        };

        return $path;
    }

    private function createAvifImage(int $width, int $height): string
    {
        $path = $this->temporaryPath('avif');
        $bytes = "\x00\x00\x00\x20".'ftypavif'.str_repeat("\x00", 16)
            ."\x00\x00\x00\x14".'ispe'.str_repeat("\x00", 4).pack('NN', $width, $height);
        file_put_contents($path, $bytes);

        return $path;
    }

    private function createIconImage(int $width, int $height): string
    {
        $path = $this->temporaryPath('ico');
        $payload = str_repeat("\x00", 64);
        $bytes = pack('vvv', 0, 1, 1)
            .pack('CCCCvvVV', $width, $height, 0, 0, 1, 32, strlen($payload), 22)
            .$payload;
        file_put_contents($path, $bytes);

        return $path;
    }

    private function temporaryPath(string $extension): string
    {
        return sys_get_temp_dir().DIRECTORY_SEPARATOR.str_replace('.', '', uniqid('nasenyi-', true)).'.'.$extension;
    }
}
