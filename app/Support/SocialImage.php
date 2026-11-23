<?php

namespace App\Support;

class SocialImage
{
    /** @return array{url: string, secureUrl: ?string, type: string, width: ?int, height: ?int, alt: string} */
    public static function make(?string $image, ?string $siteName = null): array
    {
        $path = is_string($image) && ContentHtml::safeUrl($image)
            ? $image
            : SiteBranding::image('social_image');

        $url = url($path);
        $dimensions = ImageUpload::dimensionsForPublicPath($path);
        $name = $siteName ?: config('app.name', 'Website');

        return [
            'url' => $url,
            'secureUrl' => str_starts_with($url, 'https://') ? $url : null,
            'type' => ImageUpload::mime($path),
            'width' => $dimensions['width'] ?? null,
            'height' => $dimensions['height'] ?? null,
            'alt' => $name.' social image',
        ];
    }
}
