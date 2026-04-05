<?php

namespace Database\Factories;

use App\Models\PqrsMessage;
use App\Models\PqrsRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PqrsMessage>
 */
class PqrsMessageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'pqrs_request_id' => PqrsRequest::factory(),
            'user_id' => null,
            'author_name' => fake()->name(),
            'author_email' => fake()->safeEmail(),
            'subject' => fake()->sentence(6),
            'message' => fake()->paragraph(),
            'responded_at' => now(),
            'is_internal' => false,
        ];
    }

    public function internal(): static
    {
        return $this->state(['is_internal' => true]);
    }
}
