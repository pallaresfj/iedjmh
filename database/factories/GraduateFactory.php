<?php

namespace Database\Factories;

use App\Models\Graduate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<Graduate>
 */
class GraduateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'national_id' => $this->faker->unique()->numerify('##########'),
            'full_name' => $this->faker->name(),
            'graduation_year' => (int) $this->faker->numberBetween(2014, (int) now()->format('Y')),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->numerify('3#########'),
            'current_occupation' => $this->faker->jobTitle(),
            'city' => $this->faker->city(),
            'country' => 'Colombia',
            'data_processing_consent_at' => now()->subDays(5),
            'status' => 'active',
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'activated_at' => now()->subDays(5),
            'last_login_at' => now()->subDay(),
            'academic_title' => 'Bachiller Técnico Agropecuario',
            'graduation_date' => now()->subYears(2)->startOfYear()->addMonths(11),
            'graduation_act_number' => $this->faker->bothify('ACT-####'),
            'graduation_folio' => $this->faker->bothify('FOL-####'),
            'record_verification_status' => 'verified',
        ];
    }
}
