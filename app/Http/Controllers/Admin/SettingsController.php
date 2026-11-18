<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Support\ContentHtml;
use App\Support\SiteBranding;
use App\Support\StoryMedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class SettingsController extends Controller
{
    private const PHOTO_MIMES = 'image/jpeg,image/png,image/webp,image/avif';

    private const IMAGE_MIMES = [
        'logo' => self::PHOTO_MIMES,
        'favicon' => 'image/png,image/x-icon,image/vnd.microsoft.icon',
        'social_image' => self::PHOTO_MIMES,
        'inner_header_image' => self::PHOTO_MIMES,
    ];

    public function edit(): View
    {
        return view('admin.settings', [
            'details' => SiteBranding::settings(),
            'fields' => $this->fields(),
            'imageMimes' => self::IMAGE_MIMES,
            'imageDefaults' => SiteBranding::DEFAULTS,
            'imageLimit' => StoryMedia::uploadLimit(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $rules = [];
        foreach ($this->fields() as $key => $field) {
            $rules[$key] = [($field['optional'] ?? false) ? 'sometimes' : 'present', 'nullable', 'string', ...$field['rules']];
        }
        foreach (self::IMAGE_MIMES as $key => $mimes) {
            $rules[$key.'_upload'] = ['nullable', 'file', 'mimetypes:'.$mimes, 'max:'.StoryMedia::uploadLimit()];
        }

        $data = $request->validate($rules);
        foreach (array_keys(self::IMAGE_MIMES) as $key) {
            if (filled($data[$key] ?? null) && ! ContentHtml::safeUrl($data[$key]) && ! $request->hasFile($key.'_upload')) {
                throw ValidationException::withMessages([$key => 'Use a website path or an HTTP(S) image address.']);
            }
        }

        $savedUploads = [];
        try {
            foreach (array_keys(self::IMAGE_MIMES) as $key) {
                $upload = $key.'_upload';
                unset($data[$upload]);

                if ($request->hasFile($upload)) {
                    $path = $request->file($upload)->store('content', 'public');
                    if ($path === false) {
                        throw ValidationException::withMessages([$upload => 'The image could not be saved.']);
                    }

                    $savedUploads[] = $path;
                    $data[$key] = '/storage/'.$path;
                }
            }

            DB::transaction(function () use ($data): void {
                foreach ($data as $key => $value) {
                    SiteSetting::query()->updateOrCreate(['key' => $key], ['value' => $value ?? '']);
                }
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($savedUploads);

            throw $exception;
        }

        return to_route('admin.settings.edit')->with('status', 'Website settings saved.');
    }

    /** @return array<string, array{label: string, type: string, rules: list<string>, optional?: bool, default?: string, options?: array<string, string>}> */
    private function fields(): array
    {
        $imageRules = ['max:2048'];
        $urlRules = ['max:2048', 'url:http,https'];

        return [
            'site_name' => ['label' => 'Website name', 'type' => 'text', 'rules' => ['max:255']],
            'contact_email' => ['label' => 'Contact email', 'type' => 'email', 'rules' => ['email', 'max:255']],
            'phone' => ['label' => 'Phone', 'type' => 'tel', 'rules' => ['max:255']],
            'location' => ['label' => 'Location', 'type' => 'text', 'rules' => ['max:255']],
            'facebook_url' => ['label' => 'Facebook URL', 'type' => 'url', 'rules' => $urlRules],
            'youtube_url' => ['label' => 'YouTube URL', 'type' => 'url', 'rules' => $urlRules],
            'instagram_url' => ['label' => 'Instagram URL', 'type' => 'url', 'rules' => $urlRules],
            'logo' => ['label' => 'Site logo', 'type' => 'text', 'rules' => $imageRules, 'optional' => true],
            'favicon' => ['label' => 'Site favicon', 'type' => 'text', 'rules' => $imageRules, 'optional' => true],
            'social_image' => ['label' => 'Default site image', 'type' => 'text', 'rules' => $imageRules, 'optional' => true],
            'inner_header_image' => ['label' => 'Shared inner-page banner', 'type' => 'text', 'rules' => $imageRules, 'optional' => true],
            'blog_image_ratio' => [
                'label' => 'Post card image shape',
                'type' => 'select',
                'rules' => ['in:3:2,16:9,4:3,1:1'],
                'optional' => true,
                'default' => '3:2',
                'options' => ['3:2' => 'Photo landscape (3:2)', '16:9' => 'Wide (16:9)', '4:3' => 'Classic (4:3)', '1:1' => 'Square (1:1)'],
            ],
            'blog_image_fit' => [
                'label' => 'Post card image fit',
                'type' => 'select',
                'rules' => ['in:cover,contain'],
                'optional' => true,
                'default' => 'cover',
                'options' => ['cover' => 'Fill frame', 'contain' => 'Show whole image'],
            ],
        ];
    }
}
