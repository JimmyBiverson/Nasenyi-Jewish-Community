<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Services\PesapalClient;
use App\Support\ContentHtml;
use App\Support\ImageUpload;
use App\Support\SiteBranding;
use App\Support\StoryMedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class SettingsController extends Controller
{
    private const IMAGE_KEYS = ['logo', 'favicon', 'social_image', 'inner_header_image'];

    public function edit(PesapalClient $pesapal): View
    {
        return view('admin.settings', [
            'details' => SiteBranding::settings(),
            'fields' => $this->fields(),
            'imageAccept' => ImageUpload::accept(),
            'imageDefaults' => SiteBranding::DEFAULTS,
            'imageLimit' => StoryMedia::uploadLimit(),
            'payments' => $pesapal->adminStatus(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $rules = [];
        foreach ($this->fields() as $key => $field) {
            $rules[$key] = [($field['optional'] ?? false) ? 'sometimes' : 'present', 'nullable', 'string', ...$field['rules']];
        }
        foreach (self::IMAGE_KEYS as $key) {
            $rules[$key.'_upload'] = ImageUpload::rules($key.'_upload');
        }

        $data = $request->validate($rules);
        $previous = SiteBranding::settings();

        foreach (self::IMAGE_KEYS as $key) {
            $value = $data[$key] ?? null;
            if (! filled($value) || ($request->hasFile($key.'_upload'))) {
                continue;
            }

            if (! ContentHtml::safeUrl($value) || (str_starts_with($value, '/') && ImageUpload::extension($value) === null)) {
                throw ValidationException::withMessages([$key => 'Use a website path to an image or an HTTP(S) image address.']);
            }
        }

        $savedUploads = [];
        $replacedUploads = [];
        try {
            foreach (self::IMAGE_KEYS as $key) {
                $upload = $key.'_upload';
                unset($data[$upload]);

                if (array_key_exists($key, $data) && ($previous[$key] ?? null) !== ($data[$key] ?? null)) {
                    $replacedUploads[] = $previous[$key] ?? null;
                }

                if ($request->hasFile($upload)) {
                    $path = ImageUpload::store($request->file($upload), 'content');
                    if ($path === false) {
                        throw ValidationException::withMessages([$upload => 'The image could not be saved.']);
                    }

                    $savedUploads[] = '/storage/'.$path;
                    $data[$key] = '/storage/'.$path;
                    $replacedUploads[] = $previous[$key] ?? null;
                }
            }

            DB::transaction(function () use ($data): void {
                foreach ($data as $key => $value) {
                    SiteSetting::query()->updateOrCreate(['key' => $key], ['value' => $value ?? '']);
                }
            });

            foreach (array_filter($replacedUploads) as $replacedUpload) {
                ImageUpload::delete($replacedUpload);
            }
        } catch (Throwable $exception) {
            foreach ($savedUploads as $savedUpload) {
                ImageUpload::delete($savedUpload);
            }

            if ($exception instanceof ValidationException) {
                throw $exception;
            }

            report($exception);

            return back()->withInput()->withErrors(['settings' => 'The settings could not be saved. Your previous settings are still in use.']);
        }

        return to_route('admin.settings.edit')->with('status', 'Website settings saved.');
    }

    public function payments(Request $request, PesapalClient $pesapal): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'pesapal_key' => ['nullable', 'required_with:pesapal_secret', 'string', 'max:255'],
            'pesapal_secret' => ['nullable', 'required_with:pesapal_key', 'string', 'max:255'],
            'pesapal_ipn' => ['nullable', 'uuid'],
            'pesapal_enable' => ['sometimes', 'boolean'],
            'pesapal_register' => ['sometimes', 'boolean'],
        ]);

        try {
            $pesapal->saveAdminSettings($data);
            if ($request->boolean('pesapal_register')) {
                $pesapal->saveAdminSettings(['pesapal_ipn' => $pesapal->registerIpn()]);
            }
        } catch (Throwable) {
            return back()->withErrors(['pesapal' => 'The Pesapal operation could not be completed. Check the saved status, credentials, and public HTTPS address.']);
        }

        return to_route('admin.settings.edit')->with('status', 'Pesapal settings saved.');
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
