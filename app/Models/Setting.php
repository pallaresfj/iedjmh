<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'institution_name',
        'dane',
        'nit',
        'location',
        'address',
        'phone',
        'email',
        'siee',
        'aula_virtual',
        'logo_path',
        'allies',
        'theme_primary',
        'theme_primary_dark',
        'theme_primary_light',
        'theme_accent',
        'theme_gray_900',
        'theme_gray_700',
        'theme_gray_600',
        'theme_gray_200',
        'theme_gray_100',
        'home_hero_eyebrow',
        'home_hero_title',
        'home_hero_description',
        'home_hero_cta_label',
        'home_hero_cta_url',
        'home_hero_cta_target',
        'home_hero_image_path',
        'contracting_manual_document_id',
        'singleton',
    ];

    protected function casts(): array
    {
        return [
            'singleton' => 'integer',
            'home_hero_description' => 'string',
            'allies' => 'array',
        ];
    }

    public function contractingManualDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'contracting_manual_document_id');
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
