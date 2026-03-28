<?php

namespace Database\Factories;

use App\Models\Campus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Campus>
 */
class CampusFactory extends Factory
{
    public function definition(): array
    {
        $name = 'Sede '.fake()->unique()->city();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->optional()->sentence(),
            'address' => fake()->address(),
            'phone' => fake()->optional()->phoneNumber(),
            'email' => fake()->optional()->safeEmail(),
            'latitude' => fake()->latitude(10.0, 11.0),
            'longitude' => fake()->longitude(-75.0, -74.0),
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
