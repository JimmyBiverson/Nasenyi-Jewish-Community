<?php

namespace Database\Seeders;

use App\Models\ContentEntry;
use App\Models\GalleryImage;
use App\Models\SiteSetting;
use App\Support\ContentHtml;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NasenyiContentSeeder extends Seeder
{
    public function run(): void
    {
        $entries = json_decode(
            file_get_contents(__DIR__.'/content/nasenyi.json'),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
        $gallery = json_decode(
            file_get_contents(__DIR__.'/content/gallery.json'),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        DB::transaction(function () use ($entries, $gallery): void {
            foreach ($entries as $entry) {
                $entry['body'] = ContentHtml::sanitize($entry['body']);
                ContentEntry::query()->where('source_id', $entry['source_id'])
                    ->orWhere('slug', $entry['slug'])
                    ->firstOrCreate([], $entry);
            }

            foreach ($gallery as $image) {
                GalleryImage::query()->firstOrCreate(['path' => $image['path']], $image);
            }

            foreach ($this->siteSettings() as $key => $value) {
                SiteSetting::query()->firstOrCreate(['key' => $key], ['value' => $value]);
            }
        });
    }

    /** @return array<string, string> */
    private function siteSettings(): array
    {
        return [
            'site_name' => 'Nasenyi Abayudaya Community',
            'phone' => '+256 7019 26200',
            'location' => '34M7+J3V, Mbale - Tirinyi Rd, Mbale',
            'facebook_url' => 'https://www.facebook.com/share/1c2QFYPeMR/?mibextid=wwXIfr',
            'youtube_url' => 'https://www.youtube.com/@NasenyiJews-Makor-Chayim',
            'instagram_url' => 'https://www.instagram.com/nasenyijewishcommunity',
            'logo' => '/media/brand/icon-192.png',
            'social_image' => '/media/headers/about.jpg',
        ];
    }
}
