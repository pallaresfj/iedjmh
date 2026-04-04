<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->sentence(5);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'summary' => fake()->sentence(),
            'description' => fake()->optional()->paragraph(),
            'external_url' => 'https://drive.google.com/file/d/'.Str::lower(Str::random(28)).'/view?usp=sharing',
            'document_number' => fake()->optional()->numerify('DOC-####'),
            'document_date' => fake()->optional()->date(),
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
}
