<?php

namespace Database\Factories;

use App\Models\Contract;
use App\Models\ContractDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContractDocument>
 */
class ContractDocumentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'contract_id' => Contract::factory(),
            'stage' => fake()->randomElement(['convocatoria', 'adjudicacion', 'soporte']),
            'document_type' => fake()->randomElement(['estudios_previos', 'invitacion_pliegos', 'formato_propuesta', 'acta_cierre', 'informe_evaluacion', 'acto_adjudicacion', 'otro']),
            'title' => fake()->sentence(4),
            'external_url' => fake()->url(),
            'sort_order' => 0,
        ];
    }
}
