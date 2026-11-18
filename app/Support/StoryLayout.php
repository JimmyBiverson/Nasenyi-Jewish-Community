<?php

namespace App\Support;

use App\Models\ContentEntry;
use Dom\Element;
use Dom\HTMLDocument;

class StoryLayout
{
    private const LAND_GALLERY_PHOTOS = [
        'dji-0911-150x150-b8b02ba.webp' => ['land-dji-0911.jpeg', 576],
        'kqm5499-150x150-bb4d020.webp' => ['land-kqm5499.jpeg', 684],
        'dji-0912-150x150-c51ae76.webp' => ['land-dji-0912.jpeg', 576],
        'kqm5493-150x150-0f9ba92.webp' => ['land-kqm5493.jpeg', 684],
        'kqm5494-150x150-7655c48.webp' => ['land-kqm5494.jpeg', 684],
        'kqm5505-150x150-5297172.webp' => ['land-kqm5505.jpeg', 684],
    ];

    public static function render(ContentEntry $entry): string
    {
        $body = StoryMedia::render($entry);

        if (! str_contains($body, 'story-section')) {
            return $body;
        }

        $document = HTMLDocument::createFromString($body, LIBXML_NOERROR, 'UTF-8');
        $sections = self::childrenWithClass($document->body, 'story-section');
        $pillars = null;

        foreach ($sections as $section) {
            $heading = trim($section->querySelector('h2, h3')?->textContent ?? '');
            $layout = self::layout($section, $heading, $entry->slug);

            if ($layout !== null) {
                $section->classList->add('story-layout--'.$layout);
            }
            if ($layout === 'illustrated') {
                self::makePhotoSpread($document, $section->firstElementChild);
            }

            // Group the source's adjacent pillar rows under their existing heading.
            if ($layout === 'pillars') {
                $pillars = $document->createElement('div');
                $pillars->className = 'pillars-group';
                $section->before($pillars);
                $pillars->append($section);
            } elseif ($pillars !== null && self::childrenWithClass($section, 'story-card') !== []) {
                $pillars->append($section);
            } else {
                $pillars = null;
            }
        }

        if ($entry->slug === 'about-us') {
            self::expandArchiveGallery($document);
        }

        $output = '';

        foreach ($document->body->childNodes as $child) {
            $output .= $document->saveHtml($child);
        }

        return $output;
    }

    private static function layout(Element $section, string $heading, string $slug): ?string
    {
        if ($slug === '') {
            return match ($heading) {
                'About Nasenyi' => 'opening',
                'The people who preserved our faith' => 'profile',
                'Our pillars' => 'pillars',
                'Prayer, traditions, cultural identity, learning and festivals' => 'topics',
                'A Ugandan Seder: Passover 2026 in Nasenyi' => 'seder',
                'The 8 Kilometers of Faith' => 'faith',
                'The Legacy of Leadership' => 'leadership',
                default => null,
            };
        }

        if (preg_match('/\p{Hebrew}/u', $heading) && ! $section->querySelector('img, video, iframe')) {
            return 'bilingual';
        }

        if ($heading === 'Nasenyi' && $section->querySelector('h2 + h2')) {
            return 'intro';
        }

        if ($slug === 'about-us' && str_starts_with(strtolower($heading), 'timeline block')) {
            return 'timeline';
        }

        $columns = self::childrenWithClass($section, 'story-columns')[0] ?? null;

        if ($columns !== null && count(self::childrenWithClass($columns, 'story-column')) > 1) {
            return 'spread';
        }

        if ($section->firstElementChild?->localName === 'figure' && $section->querySelector('p')) {
            return 'illustrated';
        }

        return null;
    }

    private static function makePhotoSpread(HTMLDocument $document, Element $figure): void
    {
        $spread = $document->createElement('div');
        $spread->className = 'story-photo-spread';
        $copy = $document->createElement('div');
        $copy->className = 'story-photo-copy';
        $figure->before($spread);
        $spread->append($figure, $copy);

        while ($spread->nextSibling !== null) {
            $copy->append($spread->nextSibling);
        }
    }

    private static function expandArchiveGallery(HTMLDocument $document): void
    {
        $prefix = '/media/community/';
        foreach ($document->querySelectorAll('.story-gallery img') as $image) {
            $source = (string) $image->getAttribute('src');
            $photo = str_starts_with($source, $prefix)
                ? (self::LAND_GALLERY_PHOTOS[substr($source, strlen($prefix))] ?? null)
                : null;
            if ($photo === null) {
                continue;
            }

            [$file, $height] = $photo;
            $image->setAttribute('src', $prefix.$file);
            $image->setAttribute('width', '1024');
            $image->setAttribute('height', (string) $height);
            $image->removeAttribute('srcset');
            $image->removeAttribute('sizes');

            $link = $image->closest('a');
            if ($link?->getAttribute('href') === $source) {
                $link->setAttribute('href', $prefix.$file);
            }
        }
    }

    /** @return list<Element> */
    private static function childrenWithClass(Element $element, string $class): array
    {
        $result = [];

        foreach ($element->children as $child) {
            if ($child->classList->contains($class)) {
                $result[] = $child;
            }
        }

        return $result;
    }
}
