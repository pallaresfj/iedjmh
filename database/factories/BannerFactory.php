<?php

namespace Database\Factories;

use App\Models\Banner;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Banner>
 */
class BannerFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->sentence(4);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'page_id' => null,
            'subtitle' => fake()->optional()->sentence(),
            'description' => fake()->optional()->sentence(),
            'image_path' => null,
            'cta_label' => fake()->optional()->words(2, true),
            'cta_url' => null,
            'target' => '_self',
            'status' => 'published',
            'starts_at' => null,
            'ends_at' => null,
            'created_by' => User::factory(),
            'updated_by' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(['status' => 'draft']);
    }

    public function permanent(): static
    {
        return $this->state(['starts_at' => null, 'ends_at' => null]);
    }
}
