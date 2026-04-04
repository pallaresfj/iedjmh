<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractDocument extends Model
{
    use HasFactory;

    public const STAGE_OPTIONS = [
        'convocatoria' => 'Convocatoria',
        'adjudicacion' => 'Adjudicacion',
        'soporte' => 'Soporte',
    ];

    public const DOCUMENT_TYPE_OPTIONS = [
        'estudios_previos' => 'Estudios previos',
        'invitacion_pliegos' => 'Invitacion publica / Pliegos de condiciones',
        'formato_propuesta' => 'Formato de propuesta',
        'acta_cierre' => 'Acta de cierre',
        'informe_evaluacion' => 'Informe de evaluacion',
        'acto_adjudicacion' => 'Acto administrativo de adjudicacion',
        'otro' => 'Otro',
    ];

    public const DOCUMENT_STAGE_MAP = [
        'estudios_previos' => 'convocatoria',
        'invitacion_pliegos' => 'convocatoria',
        'formato_propuesta' => 'convocatoria',
        'acta_cierre' => 'adjudicacion',
        'informe_evaluacion' => 'adjudicacion',
        'acto_adjudicacion' => 'adjudicacion',
    ];

    public const OFFICIAL_DOCUMENT_TYPES = [
        'estudios_previos',
        'invitacion_pliegos',
        'formato_propuesta',
        'acta_cierre',
        'informe_evaluacion',
        'acto_adjudicacion',
    ];

    protected $fillable = [
        'contract_id',
        'stage',
        'document_type',
        'title',
        'external_url',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public static function expectedStageFor(string $documentType): ?string
    {
        return self::DOCUMENT_STAGE_MAP[$documentType] ?? null;
    }

    /**
     * @return array<string, string>
     */
    public static function documentTypeOptionsForStage(?string $stage): array
    {
        $stage = self::normalizeCatalogValue($stage);

        if (! array_key_exists($stage, self::STAGE_OPTIONS)) {
            return [];
        }

        $options = [];

        foreach (self::DOCUMENT_TYPE_OPTIONS as $type => $label) {
            if (! self::isDocumentTypeAllowedForStage($stage, $type)) {
                continue;
            }

            $options[$type] = $label;
        }

        return $options;
    }

    public static function isDocumentTypeAllowedForStage(?string $stage, ?string $type): bool
    {
        $stage = self::normalizeCatalogValue($stage);
        $type = self::normalizeCatalogValue($type);

        if (! array_key_exists($stage, self::STAGE_OPTIONS)) {
            return false;
        }

        if (! array_key_exists($type, self::DOCUMENT_TYPE_OPTIONS)) {
            return false;
        }

        if ($type === 'otro') {
            return true;
        }

        $expectedStage = self::expectedStageFor($type);

        return $expectedStage !== null && $expectedStage === $stage;
    }

    public static function isOfficialType(string $documentType): bool
    {
        return in_array($documentType, self::OFFICIAL_DOCUMENT_TYPES, true);
    }

    public static function labelForType(string $documentType): string
    {
        return self::DOCUMENT_TYPE_OPTIONS[$documentType] ?? $documentType;
    }

    private static function normalizeCatalogValue(mixed $value): string
    {
        if (! is_string($value)) {
            return '';
        }

        return trim($value);
    }
}
