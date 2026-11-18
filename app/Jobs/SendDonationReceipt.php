<?php

namespace App\Jobs;

use App\Mail\DonationReceipt;
use App\Models\Donation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendDonationReceipt implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public int $timeout = 45;

    public function __construct(public int $donationId) {}

    /** @return list<int> */
    public function backoff(): array
    {
        return [30, 120, 600, 1800];
    }

    public function handle(): void
    {
        DB::transaction(function (): void {
            $donation = Donation::query()->lockForUpdate()->find($this->donationId);

            if ($donation === null || $donation->status !== 'completed' || $donation->receipt_sent_at) {
                return;
            }

            Mail::to($donation->donor_email)->send(new DonationReceipt($donation));
            $donation->forceFill(['receipt_sent_at' => now()])->save();
        });
    }
}
