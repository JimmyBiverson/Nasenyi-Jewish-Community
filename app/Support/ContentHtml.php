<?php

namespace App\Support;

use Dom\Element;
use Dom\HTMLDocument;
use Dom\Node;

class ContentHtml
{
    private const OMIT = ['script', 'style', 'template', 'noscript', 'svg', 'math', 'object', 'embed', 'form', 'input', 'button'];

    private const ELEMENTS = [
        'a', 'article', 'aside', 'b', 'blockquote', 'br', 'caption', 'cite', 'code', 'dd', 'details',
        'div', 'dl', 'dt', 'em', 'figcaption', 'figure', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr',
        'i', 'iframe', 'img', 'li', 'ol', 'p', 'picture', 'pre', 'section', 'small', 'source', 'span',
        'strong', 'sub', 'summary', 'sup', 'table', 'tbody', 'td', 'th', 'thead', 'tr', 'u', 'ul', 'video',
    ];

    private const VOID = ['br', 'hr', 'img', 'source'];

    public static function sanitize(string $html): string
    {
        $document = HTMLDocument::createFromString($html, LIBXML_NOERROR, 'UTF-8');

        return self::children($document->body);
    }

    public static function safeUrl(string $url, bool $link = false): bool
    {
        if ($url === '' || preg_match('/[\x00-\x20\x7f\\\\]/', $url)) {
            return false;
        }

        if (str_starts_with($url, '/')) {
            return ! str_starts_with($url, '//');
        }

        if ($link && str_starts_with($url, '#')) {
            return true;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        if ($link && in_array($scheme, ['mailto', 'tel'], true)) {
            return true;
        }

        return in_array($scheme, ['http', 'https'], true) && filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    private static function children(?Node $node): string
    {
        $html = '';

        foreach ($node?->childNodes ?? [] as $child) {
            $html .= self::render($child);
        }

        return $html;
    }

    private static function render(Node $node): string
    {
        if ($node->nodeType === XML_TEXT_NODE) {
            return e($node->textContent);
        }

        if (! $node instanceof Element) {
            return '';
        }

        $tag = $node->localName;

        if ($node->namespaceURI !== 'http://www.w3.org/1999/xhtml' || in_array($tag, self::OMIT, true)) {
            return '';
        }

        if (! in_array($tag, self::ELEMENTS, true)) {
            return self::children($node);
        }

        $attributes = self::attributes($node, $tag);

        if ($tag === 'iframe' && ! isset($attributes['src'])) {
            return '';
        }

        $opening = '<'.$tag;

        foreach ($attributes as $key => $value) {
            $opening .= ' '.$key.'="'.e($value).'"';
        }

        return $opening.'>'.(in_array($tag, self::VOID, true) ? '' : self::children($node).'</'.$tag.'>');
    }

    private static function attributes(Element $element, string $tag): array
    {
        $list = ['id', 'class', 'title', 'lang', 'dir'];
        $list = array_merge($list, match ($tag) {
            'a' => ['href'],
            'img' => ['src', 'srcset', 'sizes', 'alt', 'width', 'height', 'loading', 'decoding'],
            'iframe' => ['src', 'width', 'height'],
            'source' => ['src', 'srcset', 'sizes', 'type'],
            'video' => ['src', 'poster', 'width', 'height'],
            'td', 'th' => ['colspan', 'rowspan', 'scope'],
            'ol' => ['start'],
            default => [],
        });

        $attributes = [];

        foreach ($list as $name) {
            if (! $element->hasAttribute($name)) {
                continue;
            }

            $value = trim($element->getAttribute($name));

            if (in_array($name, ['href', 'src', 'poster'], true) && ! self::safeUrl($value, $name === 'href')) {
                continue;
            }

            if ($name === 'srcset' && ! self::safeSourceSet($value)) {
                continue;
            }

            if ($tag === 'iframe' && $name === 'src' && ! self::allowedFrame($value)) {
                continue;
            }

            $attributes[$name] = $value;
        }

        if ($tag === 'img') {
            $attributes += ['alt' => '', 'loading' => 'lazy', 'decoding' => 'async'];
        }

        if ($tag === 'video') {
            $attributes += ['controls' => '', 'preload' => 'metadata', 'playsinline' => ''];
        }

        if ($tag === 'iframe') {
            $attributes += ['title' => 'Embedded media', 'loading' => 'lazy', 'referrerpolicy' => 'strict-origin-when-cross-origin', 'allowfullscreen' => ''];
        }

        return $attributes;
    }

    private static function allowedFrame(string $url): bool
    {
        $parts = parse_url($url);

        if (($parts['scheme'] ?? '') !== 'https' || isset($parts['user']) || isset($parts['pass']) || isset($parts['port'])) {
            return false;
        }

        $host = strtolower($parts['host'] ?? '');
        $path = $parts['path'] ?? '';

        return match ($host) {
            'www.youtube.com', 'www.youtube-nocookie.com', 'youtube.com' => str_starts_with($path, '/embed/'),
            'www.google.com', 'maps.google.com' => $path === '/maps' || str_starts_with($path, '/maps/embed'),
            default => false,
        };
    }

    private static function safeSourceSet(string $value): bool
    {
        foreach (explode(',', $value) as $source) {
            $parts = preg_split('/\s+/', trim($source));

            if (! self::safeUrl($parts[0]) || count($parts) > 2
                || (isset($parts[1]) && ! preg_match('/^(?:[1-9]\d*w|(?:\d+\.)?\d+x)$/D', $parts[1]))) {
                return false;
            }
        }

        return true;
    }
}
