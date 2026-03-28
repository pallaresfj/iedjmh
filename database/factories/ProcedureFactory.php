<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Procedure;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Procedure>
 */
class ProcedureFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->sentence(3);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'summary' => fake()->sentence(),
            'requirements' => fake()->paragraph(),
            'response_time' => fake()->randomElement(['5 días hábiles', '10 días hábiles', '15 días hábiles', 'Inmediato']),
            'cost' => fake()->randomElement(['Gratuito', '$10.000', '$25.000']),
            'channel' => fake()->randomElement(['Presencial', 'Virtual', 'Mixto']),
            'is_online' => fake()->boolean(30),
            'application_url' => null,
            'contact_email' => fake()->optional()->safeEmail(),
            'contact_phone' => fake()->optional()->phoneNumber(),
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

    public function online(): static
    {
        return $this->state(['is_online' => true, 'application_url' => fake()->url()]);
    }
}
