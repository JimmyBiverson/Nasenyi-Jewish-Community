<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentEntry;
use App\Support\ContentHtml;
use App\Support\StoryMedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ContentController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->query('type') === 'post' ? 'post' : 'page';
        $search = trim($request->string('q')->limit(120)->toString());

        return view('admin.content.index', [
            'type' => $type,
            'search' => $search,
            'entries' => ContentEntry::query()
                ->where('type', $type)
                ->when($search !== '', fn ($query) => $query->where('title', 'like', '%'.$search.'%'))
                ->orderBy('title')
                ->paginate(25)
                ->withQueryString(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.content.form', [
            'entry' => new ContentEntry(['type' => $request->query('type') === 'post' ? 'post' : 'page', 'status' => 'draft']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $entry = new ContentEntry;
        $this->save($request, $entry);

        return to_route('admin.content.edit', $entry)->with('status', 'Content created.');
    }

    public function edit(ContentEntry $content): View
    {
        return view('admin.content.form', ['entry' => $content]);
    }

    public function update(Request $request, ContentEntry $content): RedirectResponse
    {
        $this->save($request, $content);

        return to_route('admin.content.edit', $content)->with('status', 'Changes saved.');
    }

    public function destroy(ContentEntry $content): RedirectResponse
    {
        abort_if($content->slug === '', 403);
        $type = $content->type;
        $content->delete();

        return to_route('admin.content.index', ['type' => $type])->with('status', 'Content deleted.');
    }

    private function save(Request $request, ContentEntry $entry): void
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(['page', 'post'])],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:191', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'body' => ['nullable', 'string', 'max:700000'],
            'summary' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'string', 'max:2048'],
            'image_upload' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:'.StoryMedia::uploadLimit()],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:600'],
            'social_image' => ['nullable', 'string', 'max:2048'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'category' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'string', 'max:2000'],
        ]);

        $slug = $data['slug'] ?? '';
        $reserved = ['admin', 'storage', 'up', 'search', 'gallery', 'events', 'donation', 'site', 'product', 'sitemap.xml', 'robots.txt'];
        $pageSlugs = ['', 'updates', 'contact-us', 'donation-history'];

        if (in_array($slug, $reserved, true) || ($data['type'] !== 'page' && in_array($slug, $pageSlugs, true))) {
            throw ValidationException::withMessages(['slug' => 'This address is reserved for the website.']);
        }

        if ($entry->exists && $entry->slug === '' && ($slug !== '' || $data['status'] !== 'published')) {
            throw ValidationException::withMessages(['slug' => 'The homepage must stay published at the root address.']);
        }

        if ($slug === '' && $data['status'] !== 'published') {
            throw ValidationException::withMessages(['slug' => 'Add an address for this draft.']);
        }

        if (ContentEntry::query()->where('slug', $slug)->when($entry->exists, fn ($query) => $query->whereKeyNot($entry->id))->exists()) {
            throw ValidationException::withMessages(['slug' => 'This address is already in use.']);
        }

        $data['slug'] = $slug;
        $data['body'] = ContentHtml::sanitize($data['body'] ?? '');
        $data['tags'] = array_values(array_unique(array_filter(array_map('trim', explode(',', $data['tags'] ?? '')))));
        $data['published_at'] = $data['status'] === 'published' ? ($entry->published_at ?? now()) : null;

        foreach (['image', 'social_image'] as $attribute) {
            if (filled($data[$attribute] ?? null) && ! ContentHtml::safeUrl($data[$attribute])) {
                throw ValidationException::withMessages([$attribute => 'Use a website path or an HTTP(S) image address.']);
            }
        }

        if ($slug === '' && $request->has('hero_slides')) {
            $slides = $request->validate(['hero_slides' => ['array', 'max:8']])['hero_slides'];
            $data['hero_slides'] = [];
            foreach ($slides as $index => $slide) {
                if (is_array($slide) && count(array_filter($slide, 'filled')) === 0) {
                    continue;
                }

                $slideKey = 'hero_slides.'.$index;
                $validator = Validator::make(['hero_slides' => [$index => $slide]], [
                    $slideKey => ['required', 'array:title,description,image,label,href'],
                    $slideKey.'.title' => ['required', 'string', 'max:255'],
                    $slideKey.'.description' => ['nullable', 'string', 'max:1000'],
                    $slideKey.'.image' => ['required', 'string', 'max:2048'],
                    $slideKey.'.label' => ['required_with:'.$slideKey.'.href', 'nullable', 'string', 'max:150'],
                    $slideKey.'.href' => ['required_with:'.$slideKey.'.label', 'nullable', 'string', 'max:2048', 'regex:~^(?:https?://[^\s]+|/(?!/)[^\s]*|#[\w-]+)$~i'],
                ]);
                $validatedSlide = $validator->validate()['hero_slides'][$index];
                if (! ContentHtml::safeUrl($validatedSlide['image'])) {
                    throw ValidationException::withMessages([$slideKey.'.image' => 'Use a website path or an HTTP(S) image address.']);
                }
                if (filled($validatedSlide['href'] ?? null) && ! ContentHtml::safeUrl($validatedSlide['href'], link: true)) {
                    throw ValidationException::withMessages([$slideKey.'.href' => 'Use a website path, an HTTP(S) address, or a page anchor.']);
                }
                $original = $entry->hero_slides[$index] ?? [];
                $crop = $validatedSlide['image'] === ($original['image'] ?? null)
                    ? array_intersect_key($original, array_flip(['position_x', 'position_y', 'fit']))
                    : [];

                $data['hero_slides'][] = $validatedSlide + $crop + ['description' => '', 'label' => '', 'href' => ''];
            }
        }

        if ($request->hasFile('image_upload')) {
            $path = $request->file('image_upload')->store('content', 'public');
            if ($path === false) {
                throw ValidationException::withMessages(['image_upload' => 'The image could not be saved.']);
            }
            $data['image'] = '/storage/'.$path;
        }

        unset($data['image_upload']);
        $entry->fill($data)->save();
    }
}
