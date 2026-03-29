<?php

namespace Database\Factories;

use App\Models\PqrsRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PqrsRequest>
 */
class PqrsRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tracking_code' => strtoupper(fake()->unique()->bothify('PQRS-####-????')),
            'type' => fake()->randomElement(['peticion', 'queja', 'reclamo', 'sugerencia', 'felicitacion']),
            'is_anonymous' => false,
            'status' => 'pendiente',
            'priority' => fake()->randomElement(['baja', 'media', 'alta']),
            'message' => fake()->paragraphs(2, true),
            'attachment_path' => null,
            'applicant_name' => fake()->name(),
            'applicant_email' => fake()->safeEmail(),
            'applicant_phone' => fake()->optional()->phoneNumber(),
            'applicant_document' => fake()->numerify('##########'),
            'applicant_address' => fake()->optional()->address(),
            'consent_habeas_data' => true,
            'submitted_at' => now(),
            'resolved_at' => null,
            'assigned_to' => null,
            'internal_notes' => null,
            'created_by' => null,
            'updated_by' => null,
        ];
    }

    public function resolved(): static
    {
        return $this->state(['status' => 'resuelto', 'resolved_at' => now()]);
    }
}
