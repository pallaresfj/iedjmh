<?php

namespace Database\Factories;

use App\Models\Graduate;
use App\Models\GraduateDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GraduateDocument>
 */
class GraduateDocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'graduate_id' => Graduate::factory(),
            'title' => $this->faker->randomElement([
                'Diploma Profesional',
                'Acta de Grado',
                'Resultados Saber 11',
            ]),
            'type_label' => $this->faker->randomElement(['diploma', 'acta', 'resultado']),
            'description' => $this->faker->sentence(),
            'drive_url' => 'https://drive.google.com/file/d/'.$this->faker->regexify('[a-zA-Z0-9_-]{24}').'/view',
            'is_official' => $this->faker->boolean(70),
            'is_visible' => true,
            'sort_order' => $this->faker->numberBetween(0, 10),
        ];
    }
}
