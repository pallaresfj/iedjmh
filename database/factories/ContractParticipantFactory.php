<?php

namespace Database\Factories;

use App\Models\Contract;
use App\Models\Contractor;
use App\Models\ContractParticipant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContractParticipant>
 */
class ContractParticipantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'contract_id' => Contract::factory(),
            'contractor_id' => Contractor::factory(),
            'name' => fake()->company(),
            'nit' => fake()->numerify('#########-#'),
            'social_object' => fake()->sentence(),
            'evaluation_score' => fake()->optional()->randomFloat(2, 0, 100),
            'is_awarded' => false,
            'sort_order' => 0,
        ];
    }

    public function awarded(): static
    {
        return $this->state(['is_awarded' => true]);
    }
}
