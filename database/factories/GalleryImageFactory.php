<?php

namespace Database\Factories;

use App\Models\GalleryImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<GalleryImage> */
class GalleryImageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4), 'path' => '/media/community/dji-0909.jpg',
            'alt_text' => 'Aerial view of Nasenyi', 'sort_order' => 0, 'is_published' => true,
        ];
    }
}
