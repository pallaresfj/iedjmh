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
        'academic_modality_label',
        'academic_modality_icon',
        'location',
        'location_latitude',
        'location_longitude',
        'address',
        'phone',
        'email',
        'contact_hours',
        'siee',
        'siee_name',
        'aula_virtual',
        'aula_virtual_name',
        'siee_document_id',
        'pei_document_id',
        'manual_convivencia_document_id',
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
        'symbols_flag_intro',
        'symbols_flag_stripes',
        'symbols_shield_intro',
        'symbols_shield_image_path',
        'symbols_shield_items',
        'symbols_hymn_title',
        'symbols_hymn_audio_path',
        'symbols_hymn_lyrics',
        'contracting_manual_document_id',
        'singleton',
    ];

    protected function casts(): array
    {
        return [
            'singleton' => 'integer',
            'location_latitude' => 'decimal:7',
            'location_longitude' => 'decimal:7',
            'contact_hours' => 'string',
            'home_hero_description' => 'string',
            'allies' => 'array',
            'symbols_flag_intro' => 'string',
            'symbols_flag_stripes' => 'array',
            'symbols_shield_intro' => 'string',
            'symbols_shield_items' => 'array',
            'symbols_hymn_lyrics' => 'string',
        ];
    }

    public function contractingManualDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'contracting_manual_document_id');
    }

    public function sieeDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'siee_document_id');
    }

    public function peiDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'pei_document_id');
    }

    public function manualConvivenciaDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'manual_convivencia_document_id');
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
