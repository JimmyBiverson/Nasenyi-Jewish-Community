<?php

namespace App\Models;

use Database\Factories\DonationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Donation extends Model
{
    /** @use HasFactory<DonationFactory> */
    use HasFactory;

    public const CURRENCY_DECIMALS = ['UGX' => 0, 'USD' => 2];

    protected $fillable = [
        'donation_cause_id',
        'reference',
        'donor_name',
        'donor_email',
        'donor_phone',
        'amount_minor',
        'currency',
        'status',
        'pesapal_tracking_id',
        'checkout_url',
        'payment_history',
        'paid_at',
        'receipt_sent_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'amount_minor' => 'integer',
            'payment_history' => 'array',
            'paid_at' => 'datetime',
            'receipt_sent_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<DonationCause, $this> */
    public function cause(): BelongsTo
    {
        return $this->belongsTo(DonationCause::class, 'donation_cause_id');
    }

    public function amount(): string
    {
        $decimals = self::CURRENCY_DECIMALS[$this->currency];
        $minorUnits = str_pad((string) $this->amount_minor, $decimals + 1, '0', STR_PAD_LEFT);

        if ($decimals === 0) {
            return $minorUnits;
        }

        return substr($minorUnits, 0, -$decimals).'.'.substr($minorUnits, -$decimals);
    }

    public static function minorUnits(mixed $amount, string $currency): ?int
    {
        if (! isset(self::CURRENCY_DECIMALS[$currency]) ||
            (! is_int($amount) && ! is_float($amount) && ! is_string($amount)) ||
            preg_match('/^([0-9]{1,9})(?:\.([0-9]{1,2}))?$/D', (string) $amount, $parts) !== 1) {
            return null;
        }

        $decimals = self::CURRENCY_DECIMALS[$currency];
        $fraction = $parts[2] ?? '';

        if (strlen(rtrim($fraction, '0')) > $decimals) {
            return null;
        }

        return ((int) $parts[1] * 10 ** $decimals) + (int) str_pad($fraction, $decimals, '0');
    }
}
