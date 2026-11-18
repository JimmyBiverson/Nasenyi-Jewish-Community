<?php

namespace Tests\Feature;

use App\Models\ContentEntry;
use App\Models\User;
use App\Support\StoryMedia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminMediaTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_draft_can_manage_a_featured_image_before_adding_copy(): void
    {
        $entry = ContentEntry::factory()->create(['status' => 'draft', 'body' => '']);
        $this->actingAs(User::factory()->create(['role' => 'editor']));
        $this->get(route('admin.content.media.edit', $entry))
            ->assertOk()
            ->assertSeeInOrder(['Featured image', 'Page banner', 'This content has no images in its body.']);
    }

    public function test_editor_can_replace_and_restore_each_section_photo_without_editing_copy(): void
    {
        Storage::fake('public');
        $body = '<section><h2>First chapter</h2><p>Prayer, traditions &amp; festivals.</p><figure><a href="/media/original.jpg">'
            .'<picture><source srcset="/media/original-small.jpg 640w"><img src="/media/original.jpg" alt="Original photo"></picture></a>'
            .'<figcaption>The original caption</figcaption></figure></section>'
            .'<section><h2>Second chapter</h2><img src="/media/original.jpg" alt="Second photo"><p>Community &amp; Spiritual Life.</p></section>';
        $entry = ContentEntry::factory()->create(['body' => $body]);
        $keys = array_keys(StoryMedia::list($body));
        $this->assertCount(2, $keys);

        $this->get(route('admin.content.media.edit', $entry))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create(['role' => 'editor']));
        $this->get(route('admin.content.media.edit', $entry))
            ->assertOk()
            ->assertSeeInOrder(['First chapter', 'Second chapter']);

        $action = route('admin.content.media.section', [$entry, $keys[0]]);
        $this->patch($action, [
            'image_upload' => UploadedFile::fake()->image('new-photo.jpg', 600, 400),
            'position_x' => 25,
            'position_y' => 80,
            'fit' => 'contain',
        ])->assertRedirect(route('admin.content.media.edit', $entry).'#image-'.$keys[0]);

        $entry->refresh();
        $this->assertSame($body, $entry->body);
        $source = $entry->body_image_overrides[$keys[0]]['image'];
        Storage::disk('public')->assertExists(str_replace('/storage/', '', $source));

        $rendered = StoryMedia::render($entry);
        $this->assertStringContainsString('src="'.$source.'"', $rendered);
        $this->assertStringContainsString('href="'.$source.'"', $rendered);
        $this->assertStringNotContainsString('original-small.jpg', $rendered);
        $this->assertSame(1, substr_count($rendered, 'src="/media/original.jpg"'));
        $this->assertStringContainsString('object-position: 25% 80%; object-fit: contain', $rendered);
        $this->assertStringContainsString('<figcaption>The original caption</figcaption>', $rendered);
        $this->withoutVite()->get('/'.$entry->slug.'/')->assertOk()->assertSeeInOrder([
            'First chapter', 'Prayer, traditions & festivals.', $source, 'The original caption',
            'Second chapter', 'Community & Spiritual Life.',
        ]);

        $this->patch($action, ['position_x' => 50, 'position_y' => 50, 'fit' => 'cover', 'reset' => '1'])
            ->assertRedirect();
        $this->assertSame($body, StoryMedia::render($entry->refresh()));
        $this->patch(route('admin.content.media.section', [$entry, str_repeat('a', 64)]), [
            'position_x' => 50, 'position_y' => 50, 'fit' => 'cover',
        ])->assertNotFound();
    }

    public function test_editor_can_reframe_slide_three_and_the_copy_editor_preserves_the_focal_point(): void
    {
        Storage::fake('public');
        $slides = array_map(fn (int $index): array => [
            'title' => 'Heading '.$index,
            'description' => 'Original words '.$index,
            'image' => '/media/slide-'.$index.'.jpg',
            'label' => 'Read the story',
            'href' => '/our-story/',
        ], range(1, 4));
        $entry = ContentEntry::factory()->create(['slug' => '', 'type' => 'page', 'status' => 'published', 'hero_slides' => $slides]);
        $action = route('admin.content.media.slide', [$entry, 2]);
        $this->actingAs(User::factory()->create(['role' => 'editor']));
        $this->get(route('admin.content.media.edit', $entry))->assertOk()->assertSeeInOrder(['Slide 1', 'Slide 2', 'Slide 3', 'Slide 4']);
        $data = [
            'image_upload' => UploadedFile::fake()->image('third-slide.jpg', 1200, 600),
            'position_x' => 35,
            'position_y' => 0,
            'fit' => 'contain',
            'slide_signature' => StoryMedia::slideSignature($slides[2]),
        ];
        $this->patch($action, $data)->assertRedirect();

        $updated = $entry->refresh()->hero_slides;
        $this->assertSame($slides[0], $updated[0]);
        $this->assertSame($slides[1], $updated[1]);
        $this->assertSame($slides[3], $updated[3]);
        $this->assertSame($slides[2]['description'], $updated[2]['description']);
        $this->assertSame($slides[2]['title'], $updated[2]['title']);
        $this->assertSame($slides[2]['href'], $updated[2]['href']);
        $this->assertSame('contain', $updated[2]['fit']);
        $this->assertSame(0, $updated[2]['position_y']);
        Storage::disk('public')->assertExists(str_replace('/storage/', '', $updated[2]['image']));
        $this->patch($action, $data)->assertSessionHasErrors('image');

        $textFields = array_map(fn (array $slide): array => array_intersect_key($slide, $slides[0]), $updated);
        $this->patch(route('admin.content.update', $entry), [
            'title' => $entry->title,
            'slug' => '',
            'type' => 'page',
            'status' => 'published',
            'body' => $entry->body,
            'hero_slides' => $textFields,
        ])->assertRedirect();
        $this->assertSame('contain', $entry->refresh()->hero_slides[2]['fit']);
    }

    public function test_admin_controls_common_post_frame_and_shared_banner_without_changing_post_images(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $settings = array_fill_keys([
            'site_name', 'contact_email', 'phone', 'location', 'facebook_url', 'youtube_url',
            'instagram_url', 'logo', 'social_image',
        ], null) + ['blog_image_ratio' => '1:1', 'blog_image_fit' => 'contain', 'inner_header_image' => '/media/shared.jpg'];
        $this->patch(route('admin.settings.update'), $settings)->assertRedirect(route('admin.settings.edit'));
        $this->assertDatabaseHas('site_settings', ['key' => 'blog_image_ratio', 'value' => '1:1']);
        $this->assertDatabaseHas('site_settings', ['key' => 'blog_image_fit', 'value' => 'contain']);
        $this->assertDatabaseHas('site_settings', ['key' => 'inner_header_image', 'value' => '/media/shared.jpg']);
        $this->get(route('admin.settings.edit'))->assertOk()->assertSeeInOrder(['Shared inner-page banner', 'Post card image shape', 'Post card image fit']);

        $entry = ContentEntry::factory()->create(['type' => 'post', 'image' => '/media/initial.jpg']);
        $this->patch(route('admin.content.media.header', $entry), ['image' => '/media/post-banner.jpg'])->assertRedirect();
        $this->assertSame('/media/post-banner.jpg', $entry->refresh()->header_image);
        $this->assertSame('/media/initial.jpg', $entry->image);
        $this->patch(route('admin.content.media.featured', $entry), [
            'image' => '/media/replacement.jpg', 'position_x' => 100, 'position_y' => 0,
        ])->assertRedirect();
        $this->assertSame('/media/replacement.jpg', $entry->refresh()->image);
        $this->assertSame(100, $entry->image_position_x);
        $this->assertSame(0, $entry->image_position_y);
    }
}
