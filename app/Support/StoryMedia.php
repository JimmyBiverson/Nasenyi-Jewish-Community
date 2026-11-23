<?php

namespace App\Support;

use App\Models\ContentEntry;
use Dom\Element;
use Dom\HTMLDocument;

class StoryMedia
{
    /** @return array<string, array{source: string, alt: string, section: string}> */
    public static function list(string $body): array
    {
        if ($body === '' || ! class_exists(HTMLDocument::class)) {
            return [];
        }

        $items = [];
        $document = self::document($body);

        foreach (self::images($document) as $key => $image) {
            $section = $image->closest('section, article');
            $heading = $section?->querySelector('h2, h3, h4, h5, h6');

            $items[$key] = [
                'source' => $image->getAttribute('src') ?? '',
                'alt' => $image->getAttribute('alt') ?? '',
                'section' => trim($heading?->textContent ?? ''),
            ];
        }

        return $items;
    }

    public static function render(ContentEntry $entry): string
    {
        $body = (string) $entry->body;
        $overrides = $entry->body_image_overrides;
        if ($body === '' || blank($overrides) || ! class_exists(HTMLDocument::class)) {
            return $body;
        }

        $document = self::document($body);
        $updated = false;
        foreach (self::images($document) as $key => $image) {
            $override = $overrides[$key] ?? null;
            if (! is_array($override)) {
                continue;
            }

            $source = $override['image'] ?? null;
            if (is_string($source) && ContentHtml::safeUrl($source) && $source !== $image->getAttribute('src')) {
                self::replaceImage($image, $source);
            }

            $x = max(0, min(100, (int) ($override['position_x'] ?? 50)));
            $y = max(0, min(100, (int) ($override['position_y'] ?? 50)));
            $fit = ($override['fit'] ?? '') === 'contain' ? 'contain' : 'cover';
            $image->setAttribute('style', "object-position: {$x}% {$y}%; object-fit: {$fit}");
            $updated = true;
        }

        return $updated ? ($document->body?->innerHTML ?? $body) : $body;
    }

    /** @param array<string, mixed> $slide */
    public static function slideSignature(array $slide): string
    {
        return hash('sha256', json_encode($slide, JSON_THROW_ON_ERROR));
    }

    /** Maximum size accepted by the mobile and desktop media forms, in KiB. */
    public static function uploadLimit(): int
    {
        $upload = intdiv(ini_parse_quantity((string) ini_get('upload_max_filesize')), 1024);
        $request = intdiv(ini_parse_quantity((string) ini_get('post_max_size')), 1024);
        $limits = [8192];

        if ($upload > 0) {
            $limits[] = $upload;
        }

        if ($request > 0) {
            $limits[] = max(1, $request - 16);
        }

        return min($limits);
    }

    private static function document(string $body): HTMLDocument
    {
        return HTMLDocument::createFromString($body, LIBXML_NOERROR, 'UTF-8');
    }

    /** @return iterable<string, Element> */
    private static function images(HTMLDocument $document): iterable
    {
        $counts = [];
        foreach ($document->body?->querySelectorAll('img') ?? [] as $image) {
            $source = $image->getAttribute('src') ?? '';
            $count = $counts[$source] = ($counts[$source] ?? 0) + 1;

            yield hash('sha256', $count.'|'.$source) => $image;
        }
    }

    private static function replaceImage(Element $image, string $source): void
    {
        $original = $image->getAttribute('src');
        $link = $image->closest('a');
        if ($original && $link?->getAttribute('href') === $original) {
            $link->setAttribute('href', $source);
        }

        $image->setAttribute('src', $source);
        foreach (['srcset', 'sizes', 'width', 'height'] as $attribute) {
            $image->removeAttribute($attribute);
        }

        $picture = $image->closest('picture');
        foreach ($picture?->querySelectorAll('source') ?? [] as $candidate) {
            $candidate->remove();
        }
    }
}
