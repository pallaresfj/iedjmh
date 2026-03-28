<?php

namespace Database\Factories;

use App\Models\Campus;
use App\Models\StaffMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StaffMember>
 */
class StaffMemberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'position_title' => fake()->jobTitle(),
            'department_label' => fake()->optional()->word(),
            'staff_group' => fake()->randomElement(['directive', 'teacher', 'administrative', 'support']),
            'campus_id' => Campus::factory(),
            'institutional_email' => fake()->unique()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'profile_photo_path' => null,
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

    public function directive(): static
    {
        return $this->state(['staff_group' => 'directive']);
    }

    public function teacher(): static
    {
        return $this->state(['staff_group' => 'teacher']);
    }
}
