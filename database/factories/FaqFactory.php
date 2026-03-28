<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Faq;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Faq>
 */
class FaqFactory extends Factory
{
    public function definition(): array
    {
        $question = fake()->sentence().'?';

        return [
            'question' => $question,
            'answer' => fake()->paragraph(),
            'slug' => Str::slug(Str::limit($question, 80, '')),
            'category_id' => Category::factory(),
            'status' => 'published',
            'published_at' => now(),
            'sort_order' => 0,
            'created_by' => User::factory(),
            'updated_by' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(['status' => 'draft', 'published_at' => null]);
    }
}
