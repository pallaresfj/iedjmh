<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->sentence(4);
        $startsAt = fake()->dateTimeBetween('+1 day', '+30 days');

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'summary' => fake()->sentence(),
            'description' => fake()->paragraphs(2, true),
            'location' => fake()->address(),
            'starts_at' => $startsAt,
            'ends_at' => fake()->dateTimeBetween($startsAt, '+31 days'),
            'is_all_day' => false,
            'registration_url' => null,
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

    public function allDay(): static
    {
        return $this->state(['is_all_day' => true]);
    }
}
