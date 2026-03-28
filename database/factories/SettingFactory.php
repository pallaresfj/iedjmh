<?php

namespace Database\Factories;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Setting>
 */
class SettingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'singleton' => 1,
            'institution_name' => 'IED Agropecuaria José María Herrera',
            'institution_nit' => '819001234-5',
            'institution_dane_code' => '147551000123',
            'rector_name' => fake()->name(),
        ];
    }
}
