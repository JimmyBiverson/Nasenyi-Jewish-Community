<?php

namespace App\Support;

use App\Models\SiteSetting;

class SiteBranding
{
    public const DEFAULTS = [
        'logo' => '/media/brand/icon-192.png',
        'favicon' => '/media/brand/icon-32.png',
        'social_image' => '/media/headers/about.jpg',
    ];

    /** @return array<string, string|null> */
    public static function settings(): array
    {
        $attributes = request()->attributes;

        if (! $attributes->has(self::class)) {
            $attributes->set(self::class, SiteSetting::pluck('value', 'key')->all());
        }

        return $attributes->get(self::class);
    }

    /** @param 'logo'|'favicon'|'social_image' $key */
    public static function image(string $key): string
    {
        $image = self::settings()[$key] ?? '';

        return ContentHtml::safeUrl($image) ? $image : self::DEFAULTS[$key];
    }
}
