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

        $this->get('/')->assertOk()->assertSeeInOrder([
            'Nasenyi Jewish Community – Makor Chayim, Uganda',
            'A Future Built Together',
            'About Nasenyi',
            'Jacob Mwosuko',
            'Our pillars',
            'A Ugandan Seder: Passover 2026 in Nasenyi',
            'The 8 Kilometers of Faith',
            'The Legacy of Leadership',
            'Latest Community Updates',
        ]);
        $this->get('/about-us/')->assertOk()->assertSeeInOrder(['1919', 'Mishael Birogi', 'Jacob Mwosuko']);
        $this->get('/checkout-2/')->assertOk()->assertSee('Heritage');
        $this->get('/my-account/')->assertOk()->assertSee('The 8 Kilometers of Faith');
        $this->get('/site/about-us/')->assertMovedPermanently()->assertRedirect(url('about-us').'/');
        $this->get('/product/donation-to-nasenyi-jewish-community')->assertMovedPermanently()->assertRedirect(url('donation').'/');
        $this->get('/probiv-test-post/')->assertNotFound();
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
