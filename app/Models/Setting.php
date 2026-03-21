<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'institution_name',
        'dane',
        'nit',
        'location',
        'siee',
        'aula_virtual',
        'logo_path',
        'singleton',
    ];

    protected function casts(): array
    {
        return [
            'singleton' => 'integer',
        ];
    }

    public static function singleton(): self
    {
        return static::query()->firstOrCreate(
            ['singleton' => 1],
            [
                'institution_name' => config('institution.display_name', config('institution.name')),
            ],
        );
    }
}
