<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\DonationCause;
use App\Services\PesapalClient;
use App\Services\PesapalDonations;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

class DonationController extends Controller
{
    public function __construct(private PesapalClient $pesapal, private PesapalDonations $payments) {}

    public function show(Request $request): View
    {
        $selectedCause = $request->query('cause');

        return view('donations.show', [
            'causes' => DonationCause::query()->where('is_active', true)->orderBy('title')->get(),
            'currencies' => $this->pesapal->currencies(),
            'available' => $this->pesapal->isConfigured(),
            'reference' => (string) Str::uuid(),
            'selectedCause' => is_string($selectedCause) ? $selectedCause : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (! $this->pesapal->isConfigured()) {
            return back()->withInput()->withErrors(['payment' => 'Online donations are temporarily unavailable. Please contact us for help giving.']);
        }

        $data = $request->validate([
            'reference' => ['required', 'uuid'],
            'cause' => ['nullable', 'string', 'max:100', Rule::exists('donation_causes', 'slug')->where('is_active', true)],
            'currency' => ['required', Rule::in($this->pesapal->currencies())],
            'amount' => ['required', 'max:12', 'string'],
            'donor_name' => ['required', 'string', 'max:160'],
            'donor_email' => ['required', 'email:rfc', 'max:254'],
            'donor_phone' => ['nullable', 'string', 'max:24', 'regex:/^\+?[0-9][0-9 ()-]{6,22}$/D'],
        ]);

        $amount = Donation::minorUnits($data['amount'], $data['currency']);
        if ($amount === null || $amount < 1) {
            throw ValidationException::withMessages(['amount' => 'Enter a positive amount in the selected currency. UGX uses whole shillings and USD supports up to two decimal places.']);
        }

        $causeId = isset($data['cause']) ? DonationCause::query()
            ->where('is_active', true)->where('slug', $data['cause'])->firstOrFail()->id : null;
        $donation = Donation::query()->firstOrCreate(['reference' => strtolower($data['reference'])], [
            'donation_cause_id' => $causeId,
            'donor_name' => $data['donor_name'],
            'donor_email' => $data['donor_email'],
            'donor_phone' => $data['donor_phone'] ?? null,
            'currency' => $data['currency'],
            'amount_minor' => $amount,
            'status' => 'pending',
            'payment_history' => [['status' => 'pending', 'source' => 'checkout', 'at' => now()->toIso8601String()]],
        ]);

        if (! $donation->wasRecentlyCreated) {
            abort_unless(
                $donation->donation_cause_id === $causeId && $donation->amount_minor === $amount &&
                    $donation->currency === $data['currency'] && hash_equals($donation->donor_email, $data['donor_email']),
                409,
            );

            if ($donation->status === 'pending' && $donation->checkout_url) {
                return redirect()->away($donation->checkout_url, 303);
            }

            return $this->statusRedirect($donation);
        }

        try {
            $order = $this->pesapal->start($donation);

            $updated = Donation::query()->whereKey($donation->id)
                ->where(fn ($query) => $query->whereNull('pesapal_tracking_id')->orWhere('pesapal_tracking_id', $order['trackingId']))
                ->update([
                    'pesapal_tracking_id' => $order['trackingId'],
                    'checkout_url' => $order['checkoutUrl'],
                ]);

            if ($updated === 0) {
                return $this->statusRedirect($donation);
            }

            return redirect()->away($order['checkoutUrl'], 303);
        } catch (RuntimeException) {
            Log::warning('Donation checkout is awaiting confirmation.', ['reference' => $donation->reference]);

            return $this->statusRedirect($donation);
        }
    }

    public function callback(Request $request): RedirectResponse
    {
        $notice = $this->readNotice($request, 'CALLBACKURL');
        abort_if($notice === null, 404);

        $donation = Donation::query()->where('reference', $notice['reference'])->firstOrFail();
        $this->verify($donation, $notice['trackingId']);

        return $this->statusRedirect($donation);
    }

    public function ipn(Request $request): JsonResponse
    {
        $notice = $this->readNotice($request, 'IPNCHANGE');
        if ($notice === null) {
            return response()->json(['status' => 400], 400);
        }

        $donation = Donation::query()->where('reference', $notice['reference'])->first();
        $status = $donation && $this->verify($donation, $notice['trackingId']) ? 200 : 500;

        return response()->json([
            'orderNotificationType' => 'IPNCHANGE',
            'orderTrackingId' => $notice['trackingId'],
            'orderMerchantReference' => $notice['reference'],
            'status' => $status,
        ], $status);
    }

    public function cancel(Donation $donation): RedirectResponse
    {
        $this->payments->cancel($donation);

        return $this->statusRedirect($donation);
    }

    public function status(Donation $donation): Response
    {
        return response()->view('donations.status', ['donation' => $donation])
            ->header('Cache-Control', 'private, no-store')
            ->header('Referrer-Policy', 'no-referrer')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    public function refreshStatus(Donation $donation): RedirectResponse
    {
        if ($donation->status === 'pending' && $donation->pesapal_tracking_id !== null &&
            ! $this->verify($donation, $donation->pesapal_tracking_id)) {
            return $this->statusRedirect($donation)
                ->with('payment_status_warning', 'We couldn’t check your payment with Pesapal. Please try again in a moment.');
        }

        return $this->statusRedirect($donation);
    }

    /** @return array{reference: string, trackingId: string}|null */
    private function readNotice(Request $request, string $expectedType): ?array
    {
        $reference = $request->query('OrderMerchantReference');
        $trackingId = $request->query('OrderTrackingId');
        $type = $request->query('OrderNotificationType', $expectedType);

        if ($type !== $expectedType || ! is_string($reference) || ! Str::isUuid($reference) ||
            ! is_string($trackingId) || ! Str::isUuid($trackingId)) {
            return null;
        }

        return ['reference' => strtolower($reference), 'trackingId' => strtolower($trackingId)];
    }

    private function verify(Donation $donation, string $trackingId): bool
    {
        try {
            $this->payments->sync($donation, $trackingId);

            return true;
        } catch (RuntimeException) {
            Log::warning('Donation payment could not be verified.', ['reference' => $donation->reference]);

            return false;
        }
    }

    private function statusRedirect(Donation $donation): RedirectResponse
    {
        return redirect()->to(URL::temporarySignedRoute('donations.status', now()->addDays(7), [
            'donation' => $donation->reference,
        ], absolute: false), 303);
    }
}
