<?php

namespace Database\Factories;

use App\Models\Donation;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Donation> */
class DonationFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'reference' => fake()->uuid(),
            'donor_name' => fake()->name(),
            'donor_email' => fake()->safeEmail(),
            'amount_minor' => 25000,
            'currency' => 'UGX',
            'status' => 'pending',
        ];
    }
}
