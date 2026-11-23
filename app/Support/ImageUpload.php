<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageUpload
{
    private const TYPES = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'webp' => 'image/webp',
        'avif' => 'image/avif',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'ico' => 'image/x-icon',
    ];

    private const MAX_WIDTH = 6000;

    private const MAX_HEIGHT = 6000;

    private const MAX_PIXELS = 16000000;

    public static function rules(string $field, bool $required = false): array
    {
        return [
            $required ? 'required' : 'nullable',
            'file',
            'max:'.StoryMedia::uploadLimit(),
            new AllowedImage,
        ];
    }

    public static function accept(): string
    {
        return '.jpg,.jpeg,.png,.webp,.avif,.gif,.bmp,.ico';
    }

    public static function store(UploadedFile $file, string $directory): string|false
    {
        $inspection = self::inspect($file->getRealPath() ?: $file->getPathname());

        if ($inspection === null) {
            return false;
        }

        return $file->storeAs($directory, Str::uuid().'.'.$inspection['extension'], 'public');
    }

    /** @return array{extension: string, mime: string, width: int, height: int}|null */
    public static function inspect(string $path): ?array
    {
        if (! is_file($path) || ! is_readable($path)) {
            return null;
        }

        $header = self::bytes($path, 0, 64);
        $type = self::typeFromBytes($header);

        if ($type === null) {
            return null;
        }

        $size = @getimagesize($path);
        $width = is_array($size) ? (int) ($size[0] ?? 0) : 0;
        $height = is_array($size) ? (int) ($size[1] ?? 0) : 0;

        if ($width < 1 || $height < 1) {
            [$width, $height] = self::fallbackDimensions($path, $header, $type['extension']);
        }

        if ($width < 1 || $height < 1 || $width > self::MAX_WIDTH || $height > self::MAX_HEIGHT || $width * $height > self::MAX_PIXELS) {
            return null;
        }

        return [
            'extension' => $type['extension'],
            'mime' => $type['mime'],
            'width' => $width,
            'height' => $height,
        ];
    }

    public static function extension(string $path): ?string
    {
        $path = parse_url($path, PHP_URL_PATH) ?: $path;
        $extension = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));

        return match ($extension) {
            'jpg', 'jpeg' => 'jpg',
            'png', 'webp', 'avif', 'gif', 'bmp', 'ico' => $extension,
            default => null,
        };
    }

    public static function mimeForExtension(string $extension): ?string
    {
        return self::TYPES[strtolower($extension)] ?? null;
    }

    public static function mime(string $path): string
    {
        return self::mimeForExtension(self::extension($path) ?? '') ?? 'image/jpeg';
    }

    /** @return array{width: int, height: int}|null */
    public static function dimensionsForPublicPath(?string $path): ?array
    {
        if (! is_string($path) || $path === '') {
            return null;
        }

        $path = parse_url($path, PHP_URL_PATH) ?: $path;
        if (str_contains($path, '..') || ! str_starts_with($path, '/')) {
            return null;
        }

        if (config('filesystems.disks.public.driver') !== 'local') {
            return null;
        }

        if (preg_match('~^/storage/(content|gallery)/[A-Za-z0-9_-]+\.(?:jpe?g|png|webp|avif|gif|bmp|ico)$~iD', $path, $matches) === 1) {
            $file = Storage::disk('public')->path($matches[1].'/'.basename($path));
            $prefix = realpath(Storage::disk('public')->path(''));
        } else {
            $file = public_path(ltrim($path, '/'));
            $prefix = realpath(public_path());
        }

        $resolved = realpath($file);
        if ($resolved === false || $prefix === false || ! str_starts_with($resolved, $prefix.DIRECTORY_SEPARATOR)) {
            return null;
        }

        $inspection = self::inspect($resolved);

        return $inspection === null ? null : ['width' => $inspection['width'], 'height' => $inspection['height']];
    }

    public static function delete(?string $path): void
    {
        if (! is_string($path)) {
            return;
        }

        $path = parse_url($path, PHP_URL_PATH) ?: $path;
        if (preg_match('~^/storage/(content|gallery)/([A-Za-z0-9_-]+\.(?:jpe?g|png|webp|avif|gif|bmp|ico))$~iD', $path, $matches) !== 1) {
            return;
        }

        if (config('filesystems.disks.public.driver') !== 'local') {
            return;
        }

        Storage::disk('public')->delete($matches[1].'/'.$matches[2]);
    }

    /** @return array{extension: string, mime: string}|null */
    private static function typeFromBytes(string $bytes): ?array
    {
        if (str_starts_with($bytes, "\xFF\xD8\xFF")) {
            return ['extension' => 'jpg', 'mime' => 'image/jpeg'];
        }

        if (str_starts_with($bytes, "\x89PNG\r\n\x1a\n")) {
            return ['extension' => 'png', 'mime' => 'image/png'];
        }

        if (str_starts_with($bytes, 'GIF87a') || str_starts_with($bytes, 'GIF89a')) {
            return ['extension' => 'gif', 'mime' => 'image/gif'];
        }

        if (str_starts_with($bytes, 'RIFF') && substr($bytes, 8, 4) === 'WEBP') {
            return ['extension' => 'webp', 'mime' => 'image/webp'];
        }

        if (substr($bytes, 4, 4) === 'ftyp' && in_array(substr($bytes, 8, 4), ['avif', 'avis'], true)) {
            return ['extension' => 'avif', 'mime' => 'image/avif'];
        }

        if (str_starts_with($bytes, 'BM')) {
            return ['extension' => 'bmp', 'mime' => 'image/bmp'];
        }

        if (str_starts_with($bytes, "\x00\x00\x01\x00")) {
            return ['extension' => 'ico', 'mime' => 'image/x-icon'];
        }

        return null;
    }

    /** @return array{0: int, 1: int} */
    private static function fallbackDimensions(string $path, string $header, string $extension): array
    {
        if ($extension === 'ico' && strlen($header) >= 8) {
            $width = ord($header[6]);
            $height = ord($header[7]);

            return [$width === 0 ? 256 : $width, $height === 0 ? 256 : $height];
        }

        if ($extension === 'bmp' && strlen($header) >= 26) {
            $width = unpack('l', substr($header, 18, 4));
            $height = unpack('l', substr($header, 22, 4));

            return [$width === false ? 0 : abs($width[1]), $height === false ? 0 : abs($height[1])];
        }

        if ($extension === 'avif') {
            $data = self::bytes($path, 0, 262144);
            $position = strpos($data, 'ispe');

            if ($position !== false && strlen($data) >= $position + 16) {
                $width = unpack('N', substr($data, $position + 8, 4));
                $height = unpack('N', substr($data, $position + 12, 4));

                return [$width === false ? 0 : $width[1], $height === false ? 0 : $height[1]];
            }
        }

        if ($extension === 'webp' && strlen($header) >= 30) {
            if (substr($header, 12, 4) === 'VP8X') {
                $width = 1 + ord($header[24]) + (ord($header[25]) << 8) + (ord($header[26]) << 16);
                $height = 1 + ord($header[27]) + (ord($header[28]) << 8) + (ord($header[29]) << 16);

                return [$width, $height];
            }

            if (substr($header, 12, 4) === 'VP8 ' && strlen($header) >= 30) {
                $width = unpack('v', substr($header, 26, 2));
                $height = unpack('v', substr($header, 28, 2));

                if ($width !== false && $height !== false) {
                    return [$width[1] & 0x3FFF, $height[1] & 0x3FFF];
                }
            }
        }

        return [0, 0];
    }

    private static function bytes(string $path, int $offset, int $length): string
    {
        $handle = @fopen($path, 'rb');

        if ($handle === false) {
            return '';
        }

        try {
            if (fseek($handle, $offset) !== 0) {
                return '';
            }

            return (string) fread($handle, $length);
        } finally {
            fclose($handle);
        }
    }
}
