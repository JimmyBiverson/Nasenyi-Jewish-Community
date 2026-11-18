<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentEntry;
use App\Models\SiteSetting;
use App\Support\ContentHtml;
use App\Support\StoryMedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ContentMediaController extends Controller
{
    public function edit(ContentEntry $content): View
    {
        return view('admin.content.media', [
            'entry' => $content,
            'images' => StoryMedia::list($content->body ?? ''),
            'display' => SiteSetting::query()
                ->whereIn('key', ['blog_image_ratio', 'blog_image_fit', 'inner_header_image'])
                ->pluck('value', 'key'),
        ]);
    }

    public function featured(Request $request, ContentEntry $content): RedirectResponse
    {
        $data = $this->validatedPhoto($request, focalPoint: true);
        $content->update([
            'image' => $this->imagePath($request, $data),
            'image_position_x' => $data['position_x'],
            'image_position_y' => $data['position_y'],
        ]);

        return $this->saved($content, 'featured', 'Featured image saved.');
    }

    public function header(Request $request, ContentEntry $content): RedirectResponse
    {
        abort_if($content->slug === '', 404);
        $data = $this->validatedPhoto($request);
        $content->update(['header_image' => $this->imagePath($request, $data)]);

        return $this->saved($content, 'banner', 'Page banner saved.');
    }

    public function section(Request $request, ContentEntry $content, string $image): RedirectResponse
    {
        $data = $this->validatedPhoto($request, focalPoint: true, fit: true);

        DB::transaction(function () use ($request, $content, $image, $data): void {
            $entry = ContentEntry::query()->whereKey($content->getKey())->lockForUpdate()->firstOrFail();
            abort_unless(isset(StoryMedia::list($entry->body ?? '')[$image]), 404);

            $overrides = $entry->body_image_overrides ?? [];
            if ($request->boolean('reset')) {
                unset($overrides[$image]);
            } else {
                $overrides[$image] = ['image' => $this->imagePath($request, $data)] + $data;
            }

            $entry->update(['body_image_overrides' => $overrides]);
        });

        return $this->saved($content, 'image-'.$image, 'Section image saved.');
    }

    public function slide(Request $request, ContentEntry $content, int $slide): RedirectResponse
    {
        abort_if($content->slug !== '', 404);
        $data = $this->validatedPhoto($request, focalPoint: true, fit: true, required: true);
        $expected = $request->validate(['slide_signature' => ['required', 'string', 'size:64']])['slide_signature'];

        DB::transaction(function () use ($request, $content, $slide, $data, $expected): void {
            $entry = ContentEntry::query()->whereKey($content->getKey())->lockForUpdate()->firstOrFail();
            $slides = $entry->hero_slides ?? [];
            abort_unless(isset($slides[$slide]), 404);

            if (! hash_equals(StoryMedia::slideSignature($slides[$slide]), $expected)) {
                throw ValidationException::withMessages(['image' => 'This slide changed. Reload the page and try again.']);
            }

            $slides[$slide] = array_replace($slides[$slide], $data, ['image' => $this->imagePath($request, $data)]);
            $entry->update(['hero_slides' => $slides]);
        });

        return $this->saved($content, 'slide-'.$slide, 'Slide image saved.');
    }

    /** @return array{image?: string|null, position_x?: int, position_y?: int, fit?: string} */
    private function validatedPhoto(Request $request, bool $focalPoint = false, bool $fit = false, bool $required = false): array
    {
        $rules = [
            'image' => [$required ? 'required_without:image_upload' : 'nullable', 'string', 'max:2048'],
            'image_upload' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:'.StoryMedia::uploadLimit()],
        ];

        if ($focalPoint) {
            $rules['position_x'] = ['required', 'integer', 'between:0,100'];
            $rules['position_y'] = ['required', 'integer', 'between:0,100'];
        }

        if ($fit) {
            $rules['fit'] = ['required', Rule::in(['cover', 'contain'])];
        }

        $data = $request->validate($rules);
        if (filled($data['image'] ?? null) && ! ContentHtml::safeUrl($data['image'])) {
            throw ValidationException::withMessages(['image' => 'Use a website path or an HTTP(S) image address.']);
        }

        unset($data['image_upload']);

        return $data;
    }

    /** @param array{image?: string|null, position_x?: int, position_y?: int, fit?: string} $data */
    private function imagePath(Request $request, array $data): ?string
    {
        if (! $request->hasFile('image_upload')) {
            return $data['image'] ?? null;
        }

        $path = $request->file('image_upload')->store('content', 'public');
        if ($path === false) {
            throw ValidationException::withMessages(['image_upload' => 'The image could not be saved.']);
        }

        return '/storage/'.$path;
    }

    private function saved(ContentEntry $content, string $anchor, string $message): RedirectResponse
    {
        return redirect(route('admin.content.media.edit', $content).'#'.$anchor)->with('status', $message);
    }
}
