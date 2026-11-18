<?php

namespace Database\Factories;

use App\Models\CommunityEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CommunityEvent> */
class CommunityEventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4), 'slug' => fake()->unique()->slug(3), 'description' => fake()->paragraph(),
            'location' => fake()->city(), 'starts_at' => now()->addWeek(), 'is_published' => true,
        ];
    }
}
