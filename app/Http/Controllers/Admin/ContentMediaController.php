<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentEntry;
use App\Models\SiteSetting;
use App\Support\ContentHtml;
use App\Support\ImageUpload;
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
        $previousImage = $content->image;
        $content->update([
            'image' => $this->imagePath($request, $data),
            'image_position_x' => $data['position_x'],
            'image_position_y' => $data['position_y'],
        ]);
        if ($previousImage && $previousImage !== $content->image) {
            ImageUpload::delete($previousImage);
        }

        return $this->saved($content, 'featured', 'Featured image saved.');
    }

    public function header(Request $request, ContentEntry $content): RedirectResponse
    {
        abort_if($content->slug === '', 404);
        $data = $this->validatedPhoto($request);
        $previousImage = $content->header_image;
        $content->update(['header_image' => $this->imagePath($request, $data)]);
        if ($previousImage && $previousImage !== $content->header_image) {
            ImageUpload::delete($previousImage);
        }

        return $this->saved($content, 'banner', 'Page banner saved.');
    }

    public function section(Request $request, ContentEntry $content, string $image): RedirectResponse
    {
        $data = $this->validatedPhoto($request, focalPoint: true, fit: true);
        $previousImage = null;

        DB::transaction(function () use ($request, $content, $image, $data, &$previousImage): void {
            $entry = ContentEntry::query()->whereKey($content->getKey())->lockForUpdate()->firstOrFail();
            abort_unless(isset(StoryMedia::list($entry->body ?? '')[$image]), 404);

            $overrides = $entry->body_image_overrides ?? [];
            $previousImage = $overrides[$image]['image'] ?? null;
            if ($request->boolean('reset')) {
                unset($overrides[$image]);
            } else {
                $overrides[$image] = ['image' => $this->imagePath($request, $data)] + $data;
            }

            $entry->update(['body_image_overrides' => $overrides]);
        });

        if ($previousImage && $previousImage !== ($content->refresh()->body_image_overrides[$image]['image'] ?? null)) {
            ImageUpload::delete($previousImage);
        }

        return $this->saved($content, 'image-'.$image, 'Section image saved.');
    }

    public function slide(Request $request, ContentEntry $content, int $slide): RedirectResponse
    {
        abort_if($content->slug !== '', 404);
        $data = $this->validatedPhoto($request, focalPoint: true, fit: true, required: true);
        $expected = $request->validate(['slide_signature' => ['required', 'string', 'size:64']])['slide_signature'];
        $previousImage = null;

        DB::transaction(function () use ($request, $content, $slide, $data, $expected, &$previousImage): void {
            $entry = ContentEntry::query()->whereKey($content->getKey())->lockForUpdate()->firstOrFail();
            $slides = $entry->hero_slides ?? [];
            abort_unless(isset($slides[$slide]), 404);

            if (! hash_equals(StoryMedia::slideSignature($slides[$slide]), $expected)) {
                throw ValidationException::withMessages(['image' => 'This slide changed. Reload the page and try again.']);
            }

            $previousImage = $slides[$slide]['image'] ?? null;
            $slides[$slide] = array_replace($slides[$slide], $data, ['image' => $this->imagePath($request, $data)]);
            $entry->update(['hero_slides' => $slides]);
        });

        if ($previousImage && $previousImage !== ($content->refresh()->hero_slides[$slide]['image'] ?? null)) {
            ImageUpload::delete($previousImage);
        }

        return $this->saved($content, 'slide-'.$slide, 'Slide image saved.');
    }

    /** @return array{image?: string|null, position_x?: int, position_y?: int, fit?: string} */
    private function validatedPhoto(Request $request, bool $focalPoint = false, bool $fit = false, bool $required = false): array
    {
        $rules = [
            'image' => [$required ? 'required_without:image_upload' : 'nullable', 'string', 'max:2048'],
            'image_upload' => ImageUpload::rules('image_upload'),
        ];

        if ($focalPoint) {
            $rules['position_x'] = ['required', 'integer', 'between:0,100'];
            $rules['position_y'] = ['required', 'integer', 'between:0,100'];
        }

        if ($fit) {
            $rules['fit'] = ['required', Rule::in(['cover', 'contain'])];
        }

        $data = $request->validate($rules);
        $image = $data['image'] ?? null;
        if (filled($image) && (! ContentHtml::safeUrl($image) || (str_starts_with($image, '/') && ImageUpload::extension($image) === null))) {
            throw ValidationException::withMessages(['image' => 'Use a website path to an image or an HTTP(S) image address.']);
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

        $path = ImageUpload::store($request->file('image_upload'), 'content');
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
