<?php

namespace Tests\Feature;

use App\Models\ContentEntry;
use App\Models\GalleryImage;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_uploads_accept_common_formats_and_reflect_on_public_site(): void
    {
        $this->withoutVite();
        Storage::fake('public');
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->patch(route('admin.settings.update'), $this->settingsPayload() + [
            'logo_upload' => UploadedFile::fake()->image('logo.gif', 64, 48),
            'favicon_upload' => UploadedFile::fake()->createWithContent('favicon.ico', $this->iconBytes(32, 32)),
            'social_image_upload' => UploadedFile::fake()->createWithContent('social.avif', $this->avifBytes(120, 63)),
            'inner_header_image_upload' => UploadedFile::fake()->image('banner.bmp', 80, 40),
        ])->assertRedirect(route('admin.settings.edit'))->assertSessionHasNoErrors();

        $images = SiteSetting::query()->whereIn('key', ['logo', 'favicon', 'social_image', 'inner_header_image'])
            ->pluck('value', 'key');

        $this->assertStringEndsWith('.gif', $images['logo']);
        $this->assertStringEndsWith('.ico', $images['favicon']);
        $this->assertStringEndsWith('.avif', $images['social_image']);
        $this->assertStringEndsWith('.bmp', $images['inner_header_image']);

        foreach ($images as $path) {
            Storage::disk('public')->assertExists(str_replace('/storage/', '', $path));
        }

        $this->get(route('site.home'))->assertOk()
            ->assertSee('<link rel="icon" href="'.$images['favicon'].'" type="image/x-icon">', false)
            ->assertSee('<img src="'.$images['logo'].'"', false)
            ->assertSee('<meta property="og:image" content="'.url($images['social_image']).'">', false)
            ->assertSee('<meta property="og:image:type" content="image/avif">', false)
            ->assertSee('<meta property="og:image:width" content="120">', false)
            ->assertSee('<meta property="og:image:height" content="63">', false);
    }

    public function test_uploaded_content_and_gallery_files_are_served(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create(['role' => 'editor']));

        $this->post(route('admin.collections.store', 'gallery'), [
            'title' => 'Synagogue',
            'alt_text' => 'The entrance to Nasenyi Synagogue.',
            'sort_order' => 1,
            'is_published' => '1',
            'image_upload' => UploadedFile::fake()->image('synagogue.png', 40, 30),
        ])->assertSessionHasNoErrors();

        $galleryImage = GalleryImage::query()->firstOrFail();
        $this->get($galleryImage->path)->assertOk()->assertHeader('Content-Type', 'image/png');

        $bytes = UploadedFile::fake()->image('sample.jpg', 40, 30)->getContent();
        Storage::disk('public')->put('content/sample.jpg', $bytes);
        $this->get('/storage/content/sample.jpg')->assertOk()->assertHeader('Content-Type', 'image/jpeg');
    }

    public function test_social_preview_always_uses_the_current_admin_image(): void
    {
        $this->withoutVite();
        Storage::fake('public');

        $first = UploadedFile::fake()->image('first.png', 120, 63)->getContent();
        $second = UploadedFile::fake()->image('second.png', 240, 126)->getContent();
        Storage::disk('public')->put('content/first.png', $first);
        Storage::disk('public')->put('content/second.png', $second);

        SiteSetting::factory()->create(['key' => 'social_image', 'value' => '/storage/content/first.png']);

        $this->get(route('site.home'))->assertOk()
            ->assertSee('<meta property="og:image" content="'.url('/storage/content/first.png').'">', false)
            ->assertSee('<meta property="og:image:width" content="120">', false);

        SiteSetting::query()->where('key', 'social_image')->update(['value' => '/storage/content/second.png']);

        $this->get(route('site.home'))->assertOk()
            ->assertSee('<meta property="og:image" content="'.url('/storage/content/second.png').'">', false)
            ->assertSee('<meta property="og:image:width" content="240">', false)
            ->assertDontSee(url('/storage/content/first.png'));

        $post = ContentEntry::factory()->create([
            'type' => 'post',
            'social_image' => '/storage/content/first.png',
        ]);

        $this->get(url($post->slug))->assertOk()
            ->assertSee('<meta property="og:image" content="'.url('/storage/content/first.png').'">', false);
    }

    public function test_invalid_image_upload_is_rejected_without_changing_settings(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->patch(route('admin.settings.update'), $this->settingsPayload() + [
            'logo' => '/media/brand/icon-192.png',
            'favicon_upload' => UploadedFile::fake()->createWithContent('fake.ico', '<!doctype html><html>Not an icon</html>'),
        ])->assertSessionHasErrors(['favicon_upload']);

        $this->assertDatabaseMissing('site_settings', ['key' => 'logo', 'value' => '/media/brand/icon-192.png']);
    }

    /** @return array<string, string|null> */
    private function settingsPayload(): array
    {
        return [
            'site_name' => 'Nasenyi Abayudaya Community',
            'contact_email' => 'contact@example.org',
            'phone' => null,
            'location' => null,
            'facebook_url' => null,
            'youtube_url' => null,
            'instagram_url' => null,
        ];
    }

    private function iconBytes(int $width, int $height): string
    {
        $payload = str_repeat("\x00", 64);

        return pack('vvv', 0, 1, 1)
            .pack('CCCCvvVV', $width, $height, 0, 0, 1, 32, strlen($payload), 22)
            .$payload;
    }

    private function avifBytes(int $width, int $height): string
    {
        return "\x00\x00\x00\x20".'ftypavif'.str_repeat("\x00", 16)
            ."\x00\x00\x00\x14".'ispe'.str_repeat("\x00", 4).pack('NN', $width, $height);
    }
}
