<?php

namespace Database\Factories;

use App\Models\Contract;
use App\Models\ContractType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contract>
 */
class ContractFactory extends Factory
{
    public function definition(): array
    {
        $publicationDate = fake()->dateTimeBetween('-30 days', '+10 days');

        return [
            'process_code' => null,
            'fiscal_year' => now()->year,
            'contract_type_id' => ContractType::factory(),
            'object' => fake()->sentence(10),
            'official_budget' => fake()->randomFloat(2, 1000000, 500000000),
            'process_status' => 'en_curso',
            'publication_date' => $publicationDate,
            'offers_deadline_date' => fake()->dateTimeBetween($publicationDate, '+30 days'),
            'evaluation_date' => null,
            'award_date' => null,
            'contractor_name' => null,
            'contractor_nit' => null,
            'contractor_social_object' => null,
            'secop_ii_url' => null,
            'status' => 'published',
            'published_at' => now(),
            'created_by' => User::factory(),
            'updated_by' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(['status' => 'draft', 'published_at' => null]);
    }

    public function awarded(): static
    {
        return $this->state([
            'process_status' => 'adjudicado',
            'award_date' => now(),
            'contractor_name' => fake()->company(),
            'contractor_nit' => fake()->numerify('#########-#'),
        ]);
    }
}
