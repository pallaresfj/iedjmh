<?php

namespace Database\Factories;

use App\Models\Campus;
use App\Models\MatriculaRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MatriculaRequest>
 */
class MatriculaRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_name' => fake()->name(),
            'grade' => fake()->randomElement(['transicion', 'primero', 'segundo', 'tercero', 'cuarto', 'quinto', 'sexto', 'septimo', 'octavo', 'noveno', 'decimo', 'undecimo']),
            'document_number' => fake()->numerify('##########'),
            'phone' => fake()->numerify('3#########'),
            'campus_id' => Campus::factory(),
            'attachments' => null,
            'status' => 'pending',
            'internal_notes' => null,
            'submitted_at' => now(),
            'reviewed_at' => null,
        ];
    }

    public function reviewed(): static
    {
        return $this->state([
            'status' => 'in_review',
            'reviewed_at' => now(),
        ]);
    }
}
