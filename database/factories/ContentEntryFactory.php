<?php

namespace Database\Factories;

use App\Models\ContentEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ContentEntry> */
class ContentEntryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'type' => 'page', 'slug' => fake()->unique()->slug(3), 'title' => fake()->sentence(4),
            'body' => '<p>'.fake()->sentence().'</p>', 'summary' => fake()->sentence(),
            'status' => 'published', 'published_at' => now()->subDay(),
        ];
    }
}
