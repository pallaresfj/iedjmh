<?php

namespace App\Support\Contracts;

use Illuminate\Support\Carbon;

class ContractTimelineValidator
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, string>
     */
    public static function validate(array $data): array
    {
        $dates = [
            'publication_date' => self::parseDate($data['publication_date'] ?? null),
            'offers_deadline_date' => self::parseDate($data['offers_deadline_date'] ?? null),
            'evaluation_date' => self::parseDate($data['evaluation_date'] ?? null),
            'award_date' => self::parseDate($data['award_date'] ?? null),
        ];

        $labels = [
            'publication_date' => 'publicación',
            'offers_deadline_date' => 'cierre de ofertas',
            'evaluation_date' => 'evaluación',
            'award_date' => 'adjudicación',
        ];

        $constraints = [
            ['earlier' => 'publication_date', 'later' => 'offers_deadline_date'],
            ['earlier' => 'publication_date', 'later' => 'evaluation_date'],
            ['earlier' => 'publication_date', 'later' => 'award_date'],
            ['earlier' => 'offers_deadline_date', 'later' => 'evaluation_date'],
            ['earlier' => 'offers_deadline_date', 'later' => 'award_date'],
            ['earlier' => 'evaluation_date', 'later' => 'award_date'],
        ];

        $errors = [];

        $dependencies = [
            ['field' => 'offers_deadline_date', 'required' => 'publication_date'],
            ['field' => 'evaluation_date', 'required' => 'offers_deadline_date'],
            ['field' => 'award_date', 'required' => 'evaluation_date'],
        ];

        foreach ($dependencies as $dependency) {
            $field = $dependency['field'];
            $required = $dependency['required'];

            if (! isset($dates[$field])) {
                continue;
            }

            if (! isset($dates[$required])) {
                $errors[$field] = sprintf(
                    'Para definir la fecha de %s debes registrar primero la fecha de %s.',
                    $labels[$field],
                    $labels[$required],
                );
            }
        }

        foreach ($constraints as $constraint) {
            $earlier = $constraint['earlier'];
            $later = $constraint['later'];

            if (! isset($dates[$earlier], $dates[$later])) {
                continue;
            }

            if ($dates[$earlier]->gt($dates[$later])) {
                $errors[$later] = sprintf(
                    'La fecha de %s no puede ser anterior a la fecha de %s.',
                    $labels[$later],
                    $labels[$earlier],
                );
            }
        }

        return $errors;
    }

    private static function parseDate(mixed $value): ?Carbon
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }
}
