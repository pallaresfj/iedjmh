<?php

namespace App\Support\Matricula;

class MatriculaOptions
{
    /**
     * @return array<string, string>
     */
    public static function gradeOptions(): array
    {
        return [
            'transicion' => 'Transición',
            'primero' => 'Primero',
            'segundo' => 'Segundo',
            'tercero' => 'Tercero',
            'cuarto' => 'Cuarto',
            'quinto' => 'Quinto',
            'sexto' => 'Sexto',
            'septimo' => 'Séptimo',
            'octavo' => 'Octavo',
            'noveno' => 'Noveno',
            'decimo' => 'Décimo',
            'undecimo' => 'Undécimo',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return [
            'pending' => 'Pendiente',
            'in_review' => 'En revision',
            'approved' => 'Aprobada',
            'rejected' => 'Rechazada',
        ];
    }
}
