<?php

namespace App\Http\Controllers;

use App\Models\CommunityEvent;
use App\Models\ContentEntry;
use App\Models\GalleryImage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SiteController extends Controller
{
    public function home(): View
    {
        return view('site.home', [
            'entry' => ContentEntry::published()->where('type', 'page')->where('slug', '')->first(),
            'latestPosts' => ContentEntry::published()->where('type', 'post')
                ->orderByDesc('published_at')->orderByDesc('id')->limit(3)->get(),
        ]);
    }

    public function page(string $slug): View
    {
        $entry = ContentEntry::published()->where('slug', $slug)->firstOrFail();

        return view('site.entry', compact('entry'));
    }

    public function updates(Request $request): View
    {
        $category = $this->queryText($request, 'category', 255);
        $tag = $this->queryText($request, 'tag', 100);
        $posts = ContentEntry::published()->where('type', 'post')
            ->when($category !== '', fn ($query) => $query->where('category', $category))
            ->when($tag !== '', fn ($query) => $query->whereJsonContains('tags', $tag))
            ->orderByDesc('published_at')->orderByDesc('id')->paginate(12)->withQueryString();

        return view('site.updates', [
            'entry' => $this->pageEntry('updates'),
            'posts' => $posts,
            'filtered' => $category !== '' || $tag !== '',
        ]);
    }

    public function gallery(): View
    {
        return view('site.gallery', [
            'entry' => $this->pageEntry('donation-history'),
            'images' => GalleryImage::where('is_published', true)->orderBy('sort_order')->orderBy('id')->paginate(24),
        ]);
    }

    public function events(): View
    {
        return view('site.events', [
            'events' => CommunityEvent::where('is_published', true)->orderBy('starts_at')->paginate(12),
        ]);
    }

    public function event(string $slug): View
    {
        return view('site.event', [
            'event' => CommunityEvent::where('is_published', true)->where('slug', $slug)->firstOrFail(),
        ]);
    }

    public function search(Request $request): View
    {
        $term = $this->queryText($request, 'q', 120);
        $matches = null;

        if ($term !== '') {
            $pattern = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term).'%';
            $matches = ContentEntry::published()
                ->where(function ($query) use ($pattern): void {
                    $query->whereRaw("title LIKE ? ESCAPE '!'", [$pattern])
                        ->orWhereRaw("summary LIKE ? ESCAPE '!'", [$pattern])
                        ->orWhereRaw("body LIKE ? ESCAPE '!'", [$pattern]);
                })
                ->orderByDesc('published_at')->orderBy('title')->paginate(12)->withQueryString();
        }

        return view('site.search', compact('term', 'matches'));
    }

    public function oldDirectory(string $slug): RedirectResponse
    {
        if (in_array($slug, ['contact-us', 'donation-history', 'updates', 'donation'], true)
            || ContentEntry::published()->where('slug', $slug)->exists()) {
            return redirect()->to(url($slug).'/', 301);
        }

        abort(404);
    }

    public function robots(): Response
    {
        $production = app()->isProduction();
        $rules = $production
            ? "User-agent: *\nDisallow: /admin/\nSitemap: ".route('site.sitemap')."\n"
            : "User-agent: *\nDisallow: /\n";

        return response($rules, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function sitemap(): Response
    {
        $urls = collect([
            url('/'), url('contact-us').'/', url('updates').'/', url('donation-history').'/', url('events').'/', url('donation').'/',
        ]);
        $entries = ContentEntry::published()->where('slug', '!=', '')->pluck('slug');
        $eventUrls = CommunityEvent::where('is_published', true)->pluck('slug')
            ->map(fn (string $slug): string => route('site.event', $slug).'/');

        $urls = $urls->concat($entries->map(fn (string $slug): string => url($slug).'/'))
            ->concat($eventUrls)->unique()->values();

        return response()->view('site.sitemap', compact('urls'), 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    private function pageEntry(string $slug): ?ContentEntry
    {
        return ContentEntry::published()->where('type', 'page')->where('slug', $slug)->first();
    }

    private function queryText(Request $request, string $key, int $length): string
    {
        $value = $request->query($key);

        return is_string($value) ? mb_substr(trim($value), 0, $length) : '';
    }
}
