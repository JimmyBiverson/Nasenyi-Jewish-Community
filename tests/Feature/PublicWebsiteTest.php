<?php

namespace Tests\Feature;

use App\Mail\ContactFormMessage;
use App\Models\CommunityEvent;
use App\Models\ContentEntry;
use App\Models\GalleryImage;
use App\Models\SiteSetting;
use Database\Seeders\NasenyiContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PublicWebsiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_original_headings_and_legacy_links_are_preserved(): void
    {
        $this->seed(NasenyiContentSeeder::class);

        $home = $this->get('/')->assertOk()->assertSeeInOrder([
            'Nasenyi Jewish Community – Makor Chayim, Uganda',
            'A Future Built Together',
            'About Nasenyi',
            'Jacob Mwosuko',
            'Our pillars',
            'Prayer, traditions, cultural identity, learning and festivals',
            'Community & Spiritual Life',
            'A Ugandan Seder: Passover 2026 in Nasenyi',
            'The 8 Kilometers of Faith',
            'The Legacy of Leadership',
            'Latest Community Updates',
        ]);
        $home->assertSee('youtube.com/embed/2_PEQmt7pGM', false)
            ->assertSee('autoplay=1', false)
            ->assertSee('mute=1', false)
            ->assertSee('Watch video')
            ->assertSee('Years of Jewish Presence')
            ->assertSee('3.5+');

        $this->get('/about-us/')->assertOk()
            ->assertSeeInOrder(['1919', 'Mishael Birogi', 'Jacob Mwosuko', 'Donation of Land for the Nasenyi Synagogue'])
            ->assertSee('/media/community/land-dji-0911.jpeg')
            ->assertSee('/media/community/land-kqm5505.jpeg')
            ->assertSee('id="pinned-cta-support"', false)
            ->assertSee(route('donations.show'));
        $this->get('/donation/')->assertOk()->assertDontSee('id="pinned-cta-support"', false);
        $this->get('/updates/')->assertOk()->assertSeeInOrder(['Nasenyi Latest Updates', 'David and Itai teaching']);
        $this->get('/checkout-2/')->assertOk()->assertSee('Heritage');
        $this->get('/my-account/')->assertOk()->assertSee('The 8 Kilometers of Faith');
        $this->get('/site/about-us/')->assertMovedPermanently()->assertRedirect(url('about-us').'/');
        $this->get('/product/donation-to-nasenyi-jewish-community')->assertMovedPermanently()->assertRedirect(url('donation').'/');
        $this->get('/probiv-test-post/')->assertNotFound();
    }

    public function test_pinned_cta_sections_render_on_public_pages(): void
    {
        $this->seed(NasenyiContentSeeder::class);

        $home = $this->get('/')->assertOk()
            ->assertSee('id="pinned-cta-visit"', false)
            ->assertSee('id="pinned-cta-support"', false)
            ->assertSee('Come encounter Nasenyi')
            ->assertSee('Support the Nasenyi Jewish Community');

        $this->assertSame(1, substr_count($home->getContent(), 'id="pinned-cta-visit"'));
        $this->assertSame(1, substr_count($home->getContent(), 'id="pinned-cta-support"'));

        $this->get('/about-us/')->assertOk()
            ->assertSee('id="pinned-cta-support"', false)
            ->assertDontSee('id="pinned-cta-visit"', false);

        $this->get('/donation/')->assertOk()
            ->assertDontSee('id="pinned-cta-support"', false);
    }

    public function test_public_images_use_shared_blog_frames_and_keep_uploaded_banner_paths(): void
    {
        SiteSetting::factory()->create(['key' => 'blog_image_ratio', 'value' => '16:9']);
        SiteSetting::factory()->create(['key' => 'blog_image_fit', 'value' => 'contain']);
        SiteSetting::factory()->create(['key' => 'inner_header_image', 'value' => '/storage/content/shared.jpg']);

        $post = ContentEntry::factory()->create([
            'type' => 'post',
            'image' => '/storage/content/post.jpg',
            'image_position_x' => 25,
            'image_position_y' => 65,
        ]);
        ContentEntry::factory()->create(['type' => 'post', 'image' => null]);

        $index = $this->get('/updates/')->assertOk()
            ->assertSee('--post-image-ratio: 16 / 9; --post-image-fit: contain')
            ->assertSee('class="page-hero"', false)
            ->assertSee('/media/headers/updates.webp')
            ->assertDontSee('/storage/content/shared.jpg')
            ->assertDontSee('/storage/content/shared.webp')
            ->assertSee('--photo-position: 25% 65%')
            ->assertSee('/storage/content/post.jpg');
        $this->assertSame(2, substr_count($index->getContent(), 'class="story-card-media"'));

        $post->update(['header_image' => '/storage/content/article-banner.jpg']);
        $this->get('/'.$post->slug.'/')->assertOk()
            ->assertSee('class="page-hero"', false)
            ->assertSee('/storage/content/article-banner.jpg')
            ->assertDontSee('/storage/content/article-banner-sm.jpg');
    }

    public function test_inner_page_heroes_use_page_specific_images(): void
    {
        SiteSetting::factory()->create(['key' => 'inner_header_image', 'value' => '/storage/content/shared.jpg']);

        $pages = [
            '/updates/' => '/media/headers/updates.webp',
            '/donation-history/' => '/media/headers/gallery.webp',
            '/contact-us/' => '/media/headers/contact.webp',
            '/events/' => '/media/headers/events.webp',
            '/search/' => '/media/headers/search.webp',
            '/donation/' => '/media/headers/donation.webp',
        ];

        foreach ($pages as $url => $image) {
            $this->get($url)->assertOk()
                ->assertSee('class="page-hero"', false)
                ->assertSee($image)
                ->assertDontSee('/storage/content/shared.jpg');
        }

        ContentEntry::factory()->create([
            'slug' => 'about-us',
            'type' => 'page',
            'header_image' => '/storage/content/custom-banner.jpg',
        ]);

        $this->get('/about-us/')->assertOk()
            ->assertSee('class="page-hero"', false)
            ->assertSee('/storage/content/custom-banner.jpg')
            ->assertDontSee('/media/headers/about.webp')
            ->assertDontSee('/storage/content/shared.jpg');
    }

    public function test_unpublished_content_is_excluded_from_public_routes_and_search(): void
    {
        ContentEntry::factory()->create(['title' => 'Nasenyi Published Story', 'slug' => 'public-story', 'type' => 'post', 'category' => 'Stories', 'tags' => ['Community']]);
        ContentEntry::factory()->create(['title' => 'Nasenyi Private Story', 'slug' => 'private-story', 'status' => 'draft']);
        ContentEntry::factory()->create(['title' => 'Nasenyi Scheduled Story', 'slug' => 'scheduled-story', 'published_at' => now()->addDay()]);

        $this->get('/private-story/')->assertNotFound();
        $this->get('/scheduled-story/')->assertNotFound();
        $this->get('/search?q=Nasenyi')->assertOk()->assertSee('Nasenyi Published Story')->assertDontSee('Nasenyi Private Story')->assertDontSee('Nasenyi Scheduled Story');
        $this->get('/updates?category=Stories&tag=Community')->assertOk()->assertSee('Nasenyi Published Story');
        $this->get('/sitemap.xml')->assertOk()->assertSee(url('public-story'))->assertDontSee('private-story')->assertDontSee('scheduled-story');
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /');
    }

    public function test_contact_form_validates_saves_and_notifies_the_configured_recipient(): void
    {
        Mail::fake();

        SiteSetting::factory()->create(['key' => 'contact_email', 'value' => 'outreach@example.com']);

        $this->post('/contact-us', ['email' => 'bad address', 'website' => 'bot'])->assertSessionHasErrors(['first_name', 'email', 'message', 'website']);
        $this->assertDatabaseCount('contact_messages', 0);

        $this->post('/contact-us', [
            'first_name' => 'Sample', 'last_name' => 'Visitor', 'email' => 'visitor@example.com', 'message' => 'How can I arrange a visit?',
        ])->assertRedirectToRoute('site.contact')->assertSessionHas('contact_status');
        $this->assertDatabaseHas('contact_messages', ['name' => 'Sample Visitor', 'email' => 'visitor@example.com']);
        Mail::assertSent(ContactFormMessage::class, fn (ContactFormMessage $mail): bool => $mail->hasTo('outreach@example.com'));
    }

    public function test_gallery_and_event_pages_expose_published_records(): void
    {
        GalleryImage::factory()->create(['alt_text' => 'Nasenyi synagogue courtyard']);
        GalleryImage::factory()->create(['is_published' => false, 'alt_text' => 'Private photograph']);
        CommunityEvent::factory()->create(['title' => 'Community study session', 'slug' => 'study-session']);
        CommunityEvent::factory()->create(['slug' => 'internal-event', 'is_published' => false]);

        $this->get('/donation-history/')->assertOk()->assertSee('Nasenyi synagogue courtyard')->assertDontSee('Private photograph');
        $this->get('/events/')->assertOk()->assertSee('Community study session');
        $this->get('/events/study-session/')->assertOk()->assertSee('Community study session');
        $this->get('/events/internal-event/')->assertNotFound();
    }
}
