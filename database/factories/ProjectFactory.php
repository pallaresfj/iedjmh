<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->sentence(4);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'summary' => fake()->sentence(),
            'description' => fake()->paragraphs(2, true),
            'starts_on' => fake()->optional()->date(),
            'ends_on' => null,
            'is_featured' => false,
            'cover_image_path' => null,
            'external_url' => null,
            'gallery_image_paths' => [],
            'status' => 'published',
            'published_at' => now(),
            'sort_order' => 0,
            'seo_title' => null,
            'seo_description' => null,
            'seo_image_path' => null,
            'created_by' => User::factory(),
            'updated_by' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(['status' => 'draft', 'published_at' => null]);
    }

    public function featured(): static
    {
        return $this->state(['is_featured' => true]);
    }
}
