<?php

namespace App\Support;

class PublicImage
{
    /**
     * @return array{source: string, narrow: ?string}
     */
    public static function banner(string $image): array
    {
        $source = $image;
        $narrow = null;

        if (preg_match('~^/media/[a-z0-9/_-]+\.(?:jpe?g|png|webp)$~iD', $image)) {
            $stem = pathinfo($image, PATHINFO_DIRNAME).'/'.pathinfo($image, PATHINFO_FILENAME);
            $source = self::existing($stem.'.webp') ?? $source;
            $narrow = self::existing($stem.'-sm.webp') ?? self::existing($stem.'-800.webp') ?? self::existing($stem.'-640.webp');
        }

        return ['source' => $source, 'narrow' => $narrow];
    }

    public static function position(mixed $value, int $default = 50): int
    {
        return is_numeric($value) ? min(100, max(0, (int) $value)) : $default;
    }

    private static function existing(string $image): ?string
    {
        return is_file(public_path(ltrim($image, '/'))) ? $image : null;
    }
}
