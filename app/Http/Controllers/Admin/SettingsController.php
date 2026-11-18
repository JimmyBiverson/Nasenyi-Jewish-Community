<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Support\ContentHtml;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings', [
            'details' => SiteSetting::query()->whereIn('key', array_keys($this->fields()))->pluck('value', 'key'),
            'fields' => $this->fields(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $rules = [];
        foreach ($this->fields() as $key => $field) {
            $rules[$key] = ['present', 'nullable', 'string', ...$field['rules']];
        }

        $data = $request->validate($rules);
        foreach (['logo', 'social_image'] as $attribute) {
            if (filled($data[$attribute] ?? null) && ! ContentHtml::safeUrl($data[$attribute])) {
                throw ValidationException::withMessages([$attribute => 'Use a website path or an HTTP(S) image address.']);
            }
        }

        DB::transaction(function () use ($data): void {
            foreach ($data as $key => $value) {
                SiteSetting::query()->updateOrCreate(['key' => $key], ['value' => $value ?? '']);
            }
        });

        return to_route('admin.settings.edit')->with('status', 'Website settings saved.');
    }

    /** @return array<string, array{label: string, type: string, rules: array<int, string>}> */
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
            'logo' => ['label' => 'Logo image path or URL', 'type' => 'text', 'rules' => $imageRules],
            'social_image' => ['label' => 'Default social sharing image path or URL', 'type' => 'text', 'rules' => $imageRules],
        ];
    }
}
