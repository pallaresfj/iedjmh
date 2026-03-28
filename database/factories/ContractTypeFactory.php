<?php

namespace Database\Factories;

use App\Models\ContractType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ContractType>
 */
class ContractTypeFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'description' => fake()->optional()->sentence(),
            'status' => 'published',
            'sort_order' => 0,
            'created_by' => User::factory(),
            'updated_by' => null,
        ];
    }
}
