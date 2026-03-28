<?php

namespace Database\Factories;

use App\Models\Page;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->sentence(4);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'menu_binding' => null,
            'summary' => fake()->optional()->sentence(),
            'content' => fake()->paragraphs(3, true),
            'status' => 'published',
            'created_by' => User::factory(),
            'updated_by' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(['status' => 'draft']);
    }

    public function withMenuBinding(string $binding): static
    {
        return $this->state(['menu_binding' => $binding]);
    }
}
