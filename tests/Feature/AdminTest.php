<?php

namespace Tests\Feature;

use App\Models\CommunityEvent;
use App\Models\ContactMessage;
use App\Models\ContentEntry;
use App\Models\Donation;
use App\Models\DonationCause;
use App\Models\GalleryImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_sign_in_and_sign_out(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->get(route('login'))->assertOk()->assertSee('Admin sign in');
        $this->post(route('admin.login.store'), ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
        $this->get(route('admin.dashboard'))->assertOk()->assertSee('Verified donations');
        $this->post(route('admin.logout'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_member_cannot_sign_in_and_editor_cannot_access_private_administration(): void
    {
        $member = User::factory()->create();

        $this->from(route('login'))
            ->post(route('admin.login.store'), ['email' => $member->email, 'password' => 'password'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->actingAs($member)->get(route('admin.dashboard'))->assertForbidden();

        $editor = User::factory()->create(['role' => 'editor']);
        $this->actingAs($editor)->get(route('admin.dashboard'))->assertOk()->assertDontSee('Verified donations');
        $this->get(route('admin.users.index'))->assertForbidden();
        $this->get(route('admin.donations.index'))->assertForbidden();
        $this->get(route('admin.inbox.index'))->assertForbidden();
        $this->patch(route('admin.settings.update'))->assertForbidden();
    }

    public function test_editor_can_manage_and_publish_a_post(): void
    {
        $editor = User::factory()->create(['role' => 'editor']);
        $data = [
            'type' => 'post',
            'title' => 'Community update',
            'slug' => 'community-update',
            'body' => '<p><strong>Shabbat</strong> in Nasenyi.</p>',
            'status' => 'draft',
            'tags' => 'traditions, community, traditions',
        ];

        $this->actingAs($editor)->get(route('admin.content.create', ['type' => 'post']))->assertOk();
        $this->post(route('admin.content.store'), $data)->assertSessionHasNoErrors();
        $entry = ContentEntry::query()->firstOrFail();
        $this->assertNull($entry->published_at);
        $this->assertSame(['traditions', 'community'], $entry->tags);
        $this->get(route('admin.content.edit', $entry))->assertOk()->assertSee('Publishing status');

        $this->patch(route('admin.content.update', $entry), [...$data, 'status' => 'published'])
            ->assertRedirect(route('admin.content.edit', $entry));
        $this->assertNotNull($entry->refresh()->published_at);
        $this->assertSame($data['body'], $entry->body);
        $this->delete(route('admin.content.destroy', $entry))->assertRedirect();
        $this->assertModelMissing($entry);
    }

    public function test_editor_can_update_home_slides_and_home_cannot_be_deleted(): void
    {
        $editor = User::factory()->create(['role' => 'editor']);
        $home = ContentEntry::factory()->create(['type' => 'page', 'slug' => '', 'status' => 'published']);
        $slide = ['title' => 'Welcome to Nasenyi', 'image' => '/media/welcome.jpg', 'label' => 'Our story', 'href' => '/about-us/'];

        $this->actingAs($editor)->get(route('admin.content.edit', $home))->assertOk()->assertSee('Homepage welcome slides');
        $this->patch(route('admin.content.update', $home), [
            'type' => 'page',
            'title' => $home->title,
            'status' => 'published',
            'body' => '<p>Welcome.</p>',
            'hero_slides' => [$slide, ['title' => '']],
        ])->assertRedirect(route('admin.content.edit', $home));
        $this->assertSame([$slide + ['description' => '']], $home->refresh()->hero_slides);
        $this->delete(route('admin.content.destroy', $home))->assertForbidden();
    }

    public function test_editor_can_upload_gallery_images_schedule_events_and_archive_causes(): void
    {
        Storage::fake('public');
        $editor = User::factory()->create(['role' => 'editor']);
        $this->actingAs($editor);

        $this->get(route('admin.collections.create', 'gallery'))->assertOk();
        $this->post(route('admin.collections.store', 'gallery'), [
            'title' => 'Synagogue',
            'alt_text' => 'The entrance to Nasenyi Synagogue.',
            'sort_order' => 1,
            'is_published' => '1',
            'image_upload' => UploadedFile::fake()->image('synagogue.jpg'),
        ])->assertSessionHasNoErrors();
        $image = GalleryImage::query()->firstOrFail();
        Storage::disk('public')->assertExists(str_replace('/storage/', '', $image->path));
        $this->assertTrue($image->is_published);

        $this->post(route('admin.collections.store', 'events'), [
            'title' => 'Community gathering',
            'slug' => 'community-gathering',
            'description' => 'An opportunity to gather.',
            'starts_at' => '2027-01-01T16:00',
            'ends_at' => '2027-01-01T18:00',
            'is_published' => '1',
        ])->assertSessionHasNoErrors();
        $event = CommunityEvent::query()->firstOrFail();
        $this->assertSame('13:00', $event->starts_at->format('H:i'));
        $this->get(route('admin.collections.edit', ['collection' => 'events', 'record' => $event->id]))
            ->assertOk()->assertSee('2027-01-01T16:00');

        $cause = DonationCause::factory()->create(['is_active' => true]);
        $this->delete(route('admin.collections.destroy', ['collection' => 'causes', 'record' => $cause->id]))
            ->assertRedirect(route('admin.collections.index', 'causes'));
        $this->assertFalse($cause->refresh()->is_active);
    }

    public function test_administrator_manages_inbox_and_reads_verified_donations(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $contact = ContactMessage::factory()->create(['read_at' => null]);
        $donation = Donation::factory()->create(['currency' => 'UGX', 'amount_minor' => 5300, 'status' => 'completed']);
        $this->actingAs($admin);

        $this->get(route('admin.inbox.index'))->assertOk()->assertSee($contact->name)->assertSee('Unread');
        $this->get(route('admin.inbox.show', $contact))->assertOk()->assertSee($contact->message);
        $this->patch(route('admin.inbox.update', $contact))->assertRedirect(route('admin.inbox.show', $contact));
        $this->assertNotNull($contact->refresh()->read_at);
        $this->get(route('admin.donations.index'))->assertOk()->assertSee('UGX 5300');
        $this->get(route('admin.donations.show', $donation))->assertOk()->assertSee($donation->reference);
        $this->patch(route('admin.donations.show', $donation), ['status' => 'failed'])->assertMethodNotAllowed();
        $this->assertSame('completed', $donation->refresh()->status);
    }

    public function test_administrator_manages_settings_and_other_accounts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        $this->get(route('admin.settings.edit'))->assertOk();
        $this->patch(route('admin.settings.update'), [
            'site_name' => 'Nasenyi Abayudaya Community',
            'contact_email' => 'contact@example.org',
            'phone' => null,
            'location' => null,
            'facebook_url' => null,
            'youtube_url' => null,
            'instagram_url' => null,
            'logo' => '/media/logo.jpg',
            'social_image' => null,
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('site_settings', ['key' => 'contact_email', 'value' => 'contact@example.org']);

        $this->get(route('admin.users.index'))->assertOk();
        $this->get(route('admin.users.create'))->assertOk();
        $this->post(route('admin.users.store'), [
            'name' => 'Community editor',
            'email' => 'editor@example.org',
            'role' => 'editor',
            'password' => 'an editor password',
            'password_confirmation' => 'an editor password',
        ])->assertSessionHasNoErrors();
        $editor = User::query()->where('email', 'editor@example.org')->firstOrFail();
        $this->assertSame('editor', $editor->role);
        $this->assertTrue(Hash::check('an editor password', $editor->password));
        $this->delete(route('admin.users.destroy', $admin))->assertForbidden();
    }
}
