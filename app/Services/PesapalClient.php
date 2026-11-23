<?php

namespace App\Services;

use App\Models\Donation;
use App\Models\SiteSetting;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use RuntimeException;

class PesapalClient
{
    public const SETTINGS_PREFIX = 'integration.pesapal.';

    private ?array $settings = null;

    private const SANDBOX_URL = 'https://cybqa.pesapal.com/pesapalv3/api';

    private const PRODUCTION_URL = 'https://pay.pesapal.com/v3/api';

    public function isConfigured(): bool
    {
        return $this->settings()['enabled']
            && $this->hasCredentials()
            && Str::isUuid((string) $this->settings()['notification_id'])
            && $this->publicUrl() !== null
            && $this->currencies() !== [];
    }

    /** @return list<string> */
    public function currencies(): array
    {
        return array_values(array_intersect(array_keys(Donation::CURRENCY_DECIMALS), config('pesapal.currencies', [])));
    }

    /** @return array{trackingId: string, checkoutUrl: string} */
    public function start(Donation $donation): array
    {
        $name = preg_split('/\s+/u', $donation->donor_name, 2);

        $data = $this->json(fn () => $this->authenticatedRequest()->post('/Transactions/SubmitOrderRequest', [
            'id' => $donation->reference,
            'currency' => $donation->currency,
            'amount' => (float) $donation->amount(),
            'description' => 'Donation to Nasenyi Jewish Community',
            'callback_url' => $this->urlFor('donations.callback'),
            'cancellation_url' => $this->publicUrl().URL::temporarySignedRoute('donations.cancel', now()->addHours(12), [
                'donation' => $donation->reference,
            ], absolute: false),
            'notification_id' => $this->settings()['notification_id'],
            'billing_address' => [
                'email_address' => $donation->donor_email,
                'phone_number' => $donation->donor_phone ?? '',
                'first_name' => $name[0],
                'last_name' => $name[1] ?? '',
            ],
        ]));

        $trackingId = $data['order_tracking_id'] ?? null;
        $checkoutUrl = $data['redirect_url'] ?? null;

        if (($data['merchant_reference'] ?? null) !== $donation->reference ||
            ! is_string($trackingId) || ! Str::isUuid($trackingId) ||
            ! is_string($checkoutUrl) || ! $this->isCheckoutUrl($checkoutUrl)) {
            throw new RuntimeException('Pesapal returned an invalid checkout response.');
        }

        return ['trackingId' => strtolower($trackingId), 'checkoutUrl' => $checkoutUrl];
    }

    /** @return array<string, mixed> */
    public function transaction(string $trackingId): array
    {
        return $this->json(fn () => $this->authenticatedRequest()->get('/Transactions/GetTransactionStatus', [
            'orderTrackingId' => $trackingId,
        ]));
    }

    public function registerIpn(): string
    {
        $url = $this->urlFor('donations.ipn');
        $data = $this->json(fn () => $this->authenticatedRequest()->post('/URLSetup/RegisterIPN', [
            'url' => $url,
            'ipn_notification_type' => 'GET',
        ]));

        $notificationId = $data['ipn_id'] ?? null;

        if (! is_string($notificationId) || ! Str::isUuid($notificationId) || ($data['url'] ?? null) !== $url) {
            throw new RuntimeException('Pesapal returned an invalid IPN registration.');
        }

        return $notificationId;
    }

    public function adminStatus(): array
    {
        $settings = $this->settings();

        return [
            'enabled' => $settings['enabled'],
            'credentials_saved' => $this->hasCredentials(),
            'notification_id' => $settings['notification_id'],
            'environment' => config('pesapal.environment'),
            'ready' => $this->isConfigured(),
        ];
    }

    public function saveAdminSettings(array $input): void
    {
        $settings = $this->settings();
        if (filled($input['pesapal_key'] ?? null)) {
            $settings['consumer_key'] = $input['pesapal_key'];
            $settings['consumer_secret'] = $input['pesapal_secret'];
            $settings['notification_id'] = null;
        }
        if (filled($input['pesapal_ipn'] ?? null)) {
            $settings['notification_id'] = $input['pesapal_ipn'];
        }

        $settings['enabled'] = ! ($input['pesapal_register'] ?? false)
            && (bool) ($input['pesapal_enable'] ?? false)
            && filled($settings['consumer_key']) && filled($settings['consumer_secret'])
            && Str::isUuid((string) $settings['notification_id']);

        SiteSetting::query()->updateOrCreate(['key' => $this->settingsKey()], [
            'value' => Crypt::encryptString(json_encode($settings, JSON_THROW_ON_ERROR)),
        ]);
        $this->settings = $settings;
    }

    private function settingsKey(): string
    {
        return self::SETTINGS_PREFIX.config('pesapal.environment');
    }

    private function settings(): array
    {
        if ($this->settings !== null) {
            return $this->settings;
        }

        $defaults = [
            'enabled' => (bool) config('pesapal.enabled'),
            'consumer_key' => config('pesapal.consumer_key'),
            'consumer_secret' => config('pesapal.consumer_secret'),
            'notification_id' => config('pesapal.notification_id'),
        ];
        $encrypted = SiteSetting::query()->where('key', $this->settingsKey())->value('value');
        if (blank($encrypted)) {
            return $this->settings = $defaults;
        }
        try {
            $stored = json_decode(Crypt::decryptString($encrypted), true, flags: JSON_THROW_ON_ERROR);
        } catch (DecryptException|\JsonException) {
            $stored = null;
        }

        return $this->settings = is_array($stored)
            ? array_replace($defaults, array_intersect_key($stored, $defaults))
            : ['enabled' => false, 'consumer_key' => null, 'consumer_secret' => null, 'notification_id' => null];
    }

    private function urlFor(string $route): string
    {
        $baseUrl = $this->publicUrl();

        if ($baseUrl === null) {
            throw new RuntimeException('Pesapal requires a public HTTPS base URL.');
        }

        return $baseUrl.route($route, absolute: false);
    }

    private function hasCredentials(): bool
    {
        $environment = config('pesapal.environment');

        return ($environment === 'sandbox' || ($environment === 'production' && app()->isProduction()))
            && filled($this->settings()['consumer_key']) && filled($this->settings()['consumer_secret']);
    }

    private function publicUrl(): ?string
    {
        $url = rtrim((string) config('pesapal.public_url'), '/');
        $parts = parse_url($url);

        if (! filter_var($url, FILTER_VALIDATE_URL) || ($parts['scheme'] ?? null) !== 'https' ||
            isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment']) || isset($parts['path']) ||
            in_array($parts['host'] ?? null, ['localhost', '127.0.0.1', '[::1]'], true)) {
            return null;
        }

        return $url;
    }

    private function authenticatedRequest(): PendingRequest
    {
        if (! $this->hasCredentials()) {
            throw new RuntimeException('Pesapal credentials are unavailable.');
        }

        $data = $this->json(fn () => $this->request()->post('/Auth/RequestToken', [
            'consumer_key' => $this->settings()['consumer_key'],
            'consumer_secret' => $this->settings()['consumer_secret'],
        ]));

        if (! is_string($data['token'] ?? null) || $data['token'] === '') {
            throw new RuntimeException('Pesapal did not issue an access token.');
        }

        return $this->request()->withToken($data['token']);
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl())
            ->asJson()
            ->acceptJson()
            ->withOptions(['allow_redirects' => false])
            ->connectTimeout(4)
            ->timeout(10);
    }

    private function baseUrl(): string
    {
        return config('pesapal.environment') === 'production' ? self::PRODUCTION_URL : self::SANDBOX_URL;
    }

    private function isCheckoutUrl(string $url): bool
    {
        $parts = parse_url($url);

        return filter_var($url, FILTER_VALIDATE_URL)
            && ($parts['scheme'] ?? null) === 'https'
            && strtolower($parts['host'] ?? '') === parse_url($this->baseUrl(), PHP_URL_HOST)
            && (! isset($parts['port']) || $parts['port'] === 443)
            && ! isset($parts['user']) && ! isset($parts['pass']);
    }

    /**
     * @param  callable(): Response  $request
     * @return array<string, mixed>
     */
    private function json(callable $request): array
    {
        try {
            $response = $request();
        } catch (ConnectionException) {
            throw new RuntimeException('Pesapal connection failed.');
        }

        $data = $response->json();

        if (! $response->successful() || ! is_array($data) ||
            ! in_array($data['status'] ?? null, [200, '200'], true) || filled(data_get($data, 'error.code'))) {
            throw new RuntimeException('Pesapal returned an unsuccessful response.');
        }

        return $data;
    }
}
