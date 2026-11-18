<?php

namespace App\Services;

use App\Jobs\SendDonationReceipt;
use App\Models\Donation;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PesapalDonations
{
    public function __construct(private PesapalClient $pesapal) {}

    public function sync(Donation $donation, string $trackingId): void
    {
        if ($donation->pesapal_tracking_id !== null && $donation->pesapal_tracking_id !== $trackingId) {
            throw new RuntimeException('The donation tracking ID does not match.');
        }

        $transaction = $this->pesapal->transaction($trackingId);
        $status = $this->confirmedStatus($donation, $transaction);

        DB::transaction(function () use ($donation, $trackingId, $status): void {
            $current = Donation::query()->lockForUpdate()->findOrFail($donation->id);

            if ($current->pesapal_tracking_id !== null && $current->pesapal_tracking_id !== $trackingId) {
                throw new RuntimeException('The donation tracking ID changed.');
            }

            $current->pesapal_tracking_id = $trackingId;

            // Late or repeated notifications must not undo a confirmed payment.
            if ($status === null || $current->status === $status ||
                ($current->status === 'completed' && $status !== 'reversed') ||
                $current->status === 'reversed') {
                $current->save();

                return;
            }

            $statusBefore = $current->status;
            if ($status === 'completed') {
                $current->paid_at = now();
            }

            $this->recordStatus($current, $status, 'pesapal');
            if ($status === 'completed' && $statusBefore !== 'completed') {
                SendDonationReceipt::dispatch($current->id)->onConnection('database')->beforeCommit();
            }
        }, attempts: 3);
    }

    public function cancel(Donation $donation): void
    {
        DB::transaction(function () use ($donation): void {
            $current = Donation::query()->lockForUpdate()->findOrFail($donation->id);

            if ($current->status === 'pending') {
                $this->recordStatus($current, 'cancelled', 'donor');
            }
        }, attempts: 3);
    }

    /** @param array<string, mixed> $transaction */
    private function confirmedStatus(Donation $donation, array $transaction): ?string
    {
        if (($transaction['merchant_reference'] ?? null) !== $donation->reference) {
            throw new RuntimeException('The merchant reference does not match.');
        }

        $code = $transaction['status_code'] ?? null;
        $description = $transaction['payment_status_description'] ?? null;
        $description = is_string($description) ? strtoupper(trim($description)) : '';

        // Pesapal can still be waiting on the payment provider after the browser returns.
        if ($description === 'PENDING' || (in_array($code, [0, '0'], true) && $description === 'INVALID')) {
            return null;
        }

        if (($transaction['currency'] ?? null) !== $donation->currency ||
            Donation::minorUnits($transaction['amount'] ?? null, $donation->currency) !== $donation->amount_minor) {
            throw new RuntimeException('The verified donation currency or amount does not match.');
        }

        $code = is_string($code) || is_int($code) ? (string) $code : '';

        return match ([$code, $description]) {
            ['1', 'COMPLETED'] => 'completed',
            ['2', 'FAILED'] => 'failed',
            ['3', 'REVERSED'] => 'reversed',
            ['', 'CANCELLED'], ['', 'CANCELED'] => 'cancelled',
            default => throw new RuntimeException('Pesapal returned an unrecognized payment status.'),
        };
    }

    private function recordStatus(Donation $donation, string $status, string $source): void
    {
        $history = $donation->payment_history ?? [];
        $history[] = ['status' => $status, 'source' => $source, 'at' => now()->toIso8601String()];

        $donation->forceFill(['status' => $status, 'payment_history' => $history])->save();
    }
}
