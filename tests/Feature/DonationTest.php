<?php

namespace Tests\Feature;

use App\Jobs\SendDonationReceipt;
use App\Mail\DonationReceipt;
use App\Models\Donation;
use App\Models\DonationCause;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

class DonationTest extends TestCase
{
    use RefreshDatabase;

    private const SANDBOX = 'https://cybqa.pesapal.com/pesapalv3/api';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set([
            'pesapal.enabled' => true,
            'pesapal.environment' => 'sandbox',
            'pesapal.consumer_key' => 'test-consumer',
            'pesapal.consumer_secret' => 'test-secret',
            'pesapal.notification_id' => '8f69231f-e317-46b8-b16c-f9ac50cecc6b',
            'pesapal.public_url' => 'https://test-site.example',
            'pesapal.currencies' => ['UGX', 'USD'],
        ]);

        Http::preventStrayRequests();
        $this->withoutVite();
    }

    public function test_donation_can_only_open_a_valid_pesapal_checkout_and_reuses_the_same_order(): void
    {
        $cause = DonationCause::factory()->create(['title' => 'Education', 'slug' => 'education', 'is_active' => true]);
        $trackingId = (string) Str::uuid();
        $checkoutUrl = "https://cybqa.pesapal.com/pesapaliframe/PesapalIframe3/Index/?OrderTrackingId=$trackingId";
        $data = $this->donationForm(['cause' => $cause->slug]);
        Http::fake([
            self::SANDBOX.'/Auth/RequestToken' => Http::response(['status' => '200', 'token' => 'access-token']),
            self::SANDBOX.'/Transactions/SubmitOrderRequest' => Http::response([
                'status' => '200',
                'merchant_reference' => $data['reference'],
                'order_tracking_id' => $trackingId,
                'redirect_url' => $checkoutUrl,
            ]),
        ]);

        $this->get(route('donations.show', ['cause' => $cause->slug]))
            ->assertOk()->assertSee($cause->title);

        $this->post(route('donations.store'), $data)->assertStatus(303)->assertRedirect($checkoutUrl);
        $this->post(route('donations.store'), $data)->assertStatus(303)->assertRedirect($checkoutUrl);
        $this->assertDatabaseCount('donations', 1);
        $this->assertDatabaseHas('donations', [
            'reference' => $data['reference'],
            'donation_cause_id' => $cause->id,
            'amount_minor' => 2575,
            'currency' => 'USD',
            'pesapal_tracking_id' => $trackingId,
            'status' => 'pending',
        ]);
        Http::assertSentCount(2);
        Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/SubmitOrderRequest') &&
            $request->hasHeader('Authorization', 'Bearer access-token') &&
            $request['id'] === $data['reference'] &&
            $request['currency'] === 'USD' &&
            $request['amount'] === 25.75 &&
            $request['callback_url'] === 'https://test-site.example/donation/callback' &&
            str_starts_with($request['cancellation_url'], 'https://test-site.example/donation/cancel/'));
    }

    public function test_invalid_amount_and_disabled_currency_never_send_the_donor_to_checkout(): void
    {
        Http::fake();
        config()->set('pesapal.currencies', ['UGX']);

        $this->post(route('donations.store'), $this->donationForm(['currency' => 'USD']))->assertSessionHasErrors('currency');
        foreach (['-5', '1.01', '0', '1e5', '1000000000'] as $amount) {
            $this->post(route('donations.store'), $this->donationForm(['amount' => $amount, 'currency' => 'UGX']))
                ->assertSessionHasErrors('amount');
        }

        $this->assertDatabaseCount('donations', 0);
        Http::assertNothingSent();
    }

    public function test_callbacks_and_duplicate_ipns_verify_payment_and_queue_one_receipt(): void
    {
        Queue::fake();
        $donation = Donation::factory()->create(['pesapal_tracking_id' => (string) Str::uuid()]);
        $this->fakeStatus($donation, ['status_code' => 1, 'payment_status_description' => 'Completed']);

        $response = $this->get($this->noticeUrl($donation, 'callback', 'CALLBACKURL').'&status=failed');
        $response->assertStatus(303);
        $this->get($response->headers->get('Location'))->assertOk()->assertSee('Payment confirmed')
            ->assertDontSee($donation->donor_email)->assertHeader('Cache-Control', 'no-store, private');
        $this->get(route('donations.status', ['donation' => $donation->reference]))->assertForbidden();

        for ($attempt = 0; $attempt < 2; $attempt++) {
            $this->get($this->noticeUrl($donation, 'ipn', 'IPNCHANGE'))->assertOk()->assertJson([
                'orderNotificationType' => 'IPNCHANGE',
                'orderTrackingId' => $donation->pesapal_tracking_id,
                'orderMerchantReference' => $donation->reference,
                'status' => 200,
            ]);
        }

        $donation->refresh();
        $this->assertSame('completed', $donation->status);
        $this->assertNotNull($donation->paid_at);
        $this->assertCount(1, $donation->payment_history);
        Queue::assertPushed(SendDonationReceipt::class, 1);
        Queue::assertPushed(SendDonationReceipt::class, fn (SendDonationReceipt $job): bool => $job->donationId === $donation->id);

        Mail::fake();
        $job = new SendDonationReceipt($donation->id);
        $job->handle();
        $job->handle();

        $this->assertNotNull($donation->refresh()->receipt_sent_at);
        Mail::assertSent(DonationReceipt::class, 1);
        Mail::assertSent(DonationReceipt::class, fn (DonationReceipt $mail): bool => $mail->hasTo($donation->donor_email));
    }

    public function test_a_notification_cannot_complete_a_donation_with_the_wrong_reference_amount_or_currency(): void
    {
        Queue::fake();
        $donation = Donation::factory()->create(['pesapal_tracking_id' => (string) Str::uuid()]);

        foreach (['merchant_reference' => (string) Str::uuid(), 'amount' => 25001, 'currency' => 'USD'] as $field => $value) {
            $this->fakeStatus($donation, [
                'status_code' => 1,
                'payment_status_description' => 'Completed',
                $field => $value,
            ]);
            $this->get($this->noticeUrl($donation, 'ipn', 'IPNCHANGE'))->assertInternalServerError()->assertJson(['status' => 500]);
            $this->assertSame('pending', $donation->refresh()->status);
        }

        $url = route('donations.ipn', [
            'OrderTrackingId' => (string) Str::uuid(),
            'OrderMerchantReference' => $donation->reference,
        ]);
        $this->get($url)->assertInternalServerError()->assertJson(['status' => 500]);
        $this->assertNull($donation->refresh()->paid_at);
        Queue::assertNothingPushed();
    }

    public function test_pending_can_be_cancelled_and_a_late_payment_is_still_recognized(): void
    {
        Queue::fake();
        $donation = Donation::factory()->create(['pesapal_tracking_id' => (string) Str::uuid()]);
        $this->fakeStatus($donation, ['status_code' => 0, 'payment_status_description' => 'Invalid']);
        $this->get($this->noticeUrl($donation, 'callback', 'CALLBACKURL'))->assertStatus(303);
        $this->assertSame('pending', $donation->refresh()->status);
        Queue::assertNothingPushed();

        $this->get(route('donations.cancel', ['donation' => $donation->reference]))->assertForbidden();
        $cancel = URL::temporarySignedRoute('donations.cancel', now()->addMinutes(10), [
            'donation' => $donation->reference,
        ], absolute: false);
        $this->get($cancel.'&OrderTrackingId='.$donation->pesapal_tracking_id)->assertStatus(303);
        $this->assertSame('cancelled', $donation->refresh()->status);

        $this->fakeStatus($donation, ['status_code' => '1', 'payment_status_description' => 'Completed']);
        $this->get($this->noticeUrl($donation, 'ipn', 'IPNCHANGE'))->assertOk();
        $this->assertSame('completed', $donation->refresh()->status);

        $this->fakeStatus($donation, ['status_code' => '3', 'payment_status_description' => 'Reversed']);
        $this->get($this->noticeUrl($donation, 'ipn', 'IPNCHANGE'))->assertOk();
        $this->assertSame('reversed', $donation->refresh()->status);
        $this->assertNotNull($donation->paid_at);
        Queue::assertPushed(SendDonationReceipt::class, 1);
    }

    public function test_a_donor_can_check_a_pending_payment_when_the_ipn_has_not_arrived(): void
    {
        $donation = Donation::factory()->create(['pesapal_tracking_id' => (string) Str::uuid()]);
        $this->fakeStatus($donation, ['status_code' => 0, 'payment_status_description' => 'Invalid']);

        $callback = $this->get($this->noticeUrl($donation, 'callback', 'CALLBACKURL'))->assertStatus(303);
        $this->get($callback->headers->get('Location'))->assertOk()->assertSee('Check payment status');
        $this->assertSame('pending', $donation->refresh()->status);

        $route = ['donation' => $donation->reference];
        $this->post(route('donations.refresh', $route))->assertForbidden();
        $signedRefresh = URL::temporarySignedRoute('donations.refresh', now()->addMinutes(15), $route, absolute: false);

        $this->fakeStatus($donation, ['status' => '503']);
        $this->post($signedRefresh)->assertStatus(303)->assertSessionHas('payment_status_warning');
        $this->assertSame('pending', $donation->refresh()->status);
        $this->assertDatabaseCount('jobs', 0);

        $this->fakeStatus($donation, ['status_code' => 1, 'payment_status_description' => 'Completed']);
        $result = $this->post($signedRefresh)->assertStatus(303);
        $this->get($result->headers->get('Location'))->assertOk()->assertSee('Payment confirmed')->assertDontSee('Check payment status');
        $this->post($signedRefresh)->assertStatus(303);

        $this->assertSame('completed', $donation->refresh()->status);
        Http::assertSentCount(2);
        $this->assertDatabaseCount('jobs', 1);
    }

    public function test_a_verified_failure_is_recorded_without_a_receipt(): void
    {
        Queue::fake();
        $donation = Donation::factory()->create(['pesapal_tracking_id' => (string) Str::uuid()]);
        $this->fakeStatus($donation, ['status_code' => '2', 'payment_status_description' => 'Failed']);

        $response = $this->get($this->noticeUrl($donation, 'callback', 'CALLBACKURL'));
        $response->assertStatus(303);
        $this->get($response->headers->get('Location'))->assertOk()->assertSee('Payment failed');
        $this->assertSame('failed', $donation->refresh()->status);
        $this->assertNull($donation->paid_at);
        Queue::assertNothingPushed();
    }

    public function test_ipn_registration_uses_the_public_https_url(): void
    {
        Http::fake([
            self::SANDBOX.'/Auth/RequestToken' => Http::response(['status' => '200', 'token' => 'access-token']),
            self::SANDBOX.'/URLSetup/RegisterIPN' => Http::response([
                'status' => '200',
                'url' => 'https://test-site.example/donation/ipn',
                'ipn_id' => '8f69231f-e317-46b8-b16c-f9ac50cecc6b',
            ]),
        ]);

        $this->artisan('pesapal:register-ipn')
            ->expectsOutput('Set PESAPAL_NOTIFICATION_ID=8f69231f-e317-46b8-b16c-f9ac50cecc6b')
            ->assertSuccessful();
        Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/RegisterIPN') &&
            $request['url'] === 'https://test-site.example/donation/ipn' && $request['ipn_notification_type'] === 'GET');
    }

    public function test_checkout_recovers_safely_from_an_untrusted_redirect_or_gateway_connection_failure(): void
    {
        Http::fake([
            self::SANDBOX.'/Auth/RequestToken' => Http::response(['status' => '200', 'token' => 'access-token']),
            self::SANDBOX.'/Transactions/SubmitOrderRequest' => Http::response([
                'status' => '200',
                'merchant_reference' => '64d6dfd1-48db-44cc-b142-d4e7d367cd01',
                'order_tracking_id' => (string) Str::uuid(),
                'redirect_url' => 'https://cybqa.pesapal.com.example.test/misleading',
            ]),
        ]);
        $reference = '64d6dfd1-48db-44cc-b142-d4e7d367cd01';
        $response = $this->post(route('donations.store'), $this->donationForm(['reference' => $reference]));
        $response->assertStatus(303);
        $this->assertStringContainsString('/donation/status/'.$reference, $response->headers->get('Location'));
        $this->assertDatabaseHas('donations', ['reference' => $reference, 'status' => 'pending', 'pesapal_tracking_id' => null]);

        Http::swap(new Factory);
        Http::preventStrayRequests();
        Http::fake([
            self::SANDBOX.'/Auth/RequestToken' => Http::response(['status' => '200', 'token' => 'access-token']),
            self::SANDBOX.'/Transactions/SubmitOrderRequest' => Http::failedConnection(),
        ]);
        $form = $this->donationForm();
        $response = $this->post(route('donations.store'), $form);
        $response->assertStatus(303);
        $this->assertStringContainsString('/donation/status/'.$form['reference'], $response->headers->get('Location'));
    }

    public function test_checkout_is_unavailable_without_configuration_and_no_live_checkout_runs_locally(): void
    {
        Http::fake();
        config()->set('pesapal.enabled', false);
        $this->get(route('donations.show'))->assertOk()->assertSee('Online donations are temporarily unavailable');
        $this->post(route('donations.store'), $this->donationForm())->assertSessionHasErrors('payment');

        config()->set(['pesapal.enabled' => true, 'pesapal.environment' => 'production']);
        $this->post(route('donations.store'), $this->donationForm())->assertSessionHasErrors('payment');
        $this->assertDatabaseCount('donations', 0);
        Http::assertNothingSent();
    }

    /**
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function donationForm(array $overrides = []): array
    {
        return array_replace([
            'reference' => (string) Str::uuid(),
            'donor_name' => 'Sample Donor',
            'donor_email' => 'donor@example.com',
            'currency' => 'USD',
            'amount' => '25.75',
        ], $overrides);
    }

    private function noticeUrl(Donation $donation, string $route, string $type): string
    {
        return route('donations.'.$route, [
            'OrderTrackingId' => $donation->pesapal_tracking_id,
            'OrderMerchantReference' => $donation->reference,
            'OrderNotificationType' => $type,
        ]);
    }

    /** @param array<string, mixed> $overrides */
    private function fakeStatus(Donation $donation, array $overrides): void
    {
        Http::swap(new Factory);
        Http::preventStrayRequests();
        Http::fake([
            self::SANDBOX.'/Auth/RequestToken' => Http::response(['status' => '200', 'token' => 'access-token']),
            self::SANDBOX.'/Transactions/GetTransactionStatus?orderTrackingId='.$donation->pesapal_tracking_id => Http::response(array_replace([
                'status' => '200',
                'merchant_reference' => $donation->reference,
                'currency' => $donation->currency,
                'amount' => $donation->amount(),
            ], $overrides)),
        ]);
    }
}
