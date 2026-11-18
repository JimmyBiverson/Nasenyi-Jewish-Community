<?php

namespace Database\Factories;

use App\Models\DonationCause;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DonationCause> */
class DonationCauseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4), 'slug' => fake()->unique()->slug(3),
            'description' => fake()->sentence(), 'is_active' => true,
        ];
    }
}
