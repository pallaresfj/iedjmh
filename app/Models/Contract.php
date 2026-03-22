<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use HasFactory, SoftDeletes;

    public const PROCESS_STATUS_OPTIONS = [
        'en_curso' => 'En curso',
        'adjudicado' => 'Adjudicado',
        'desierto' => 'Desierto',
        'finalizado' => 'Finalizado',
    ];

    public const STATUS_OPTIONS = [
        'draft' => 'Borrador',
        'published' => 'Publicado',
        'archived' => 'Archivado',
    ];

    protected $fillable = [
        'process_code',
        'fiscal_year',
        'contract_type_id',
        'object',
        'official_budget',
        'process_status',
        'publication_date',
        'offers_deadline_date',
        'evaluation_date',
        'award_date',
        'contractor_name',
        'contractor_nit',
        'contractor_social_object',
        'secop_ii_url',
        'status',
        'published_at',
        'created_by',
        'updated_by',
    ];

    protected static function booted(): void
    {
        static::saving(function (Contract $contract): void {
            if (blank($contract->process_code) && filled($contract->fiscal_year)) {
                $contract->process_code = self::nextProcessCode((int) $contract->fiscal_year, $contract->exists ? (int) $contract->getKey() : null);
            }

            if ($contract->status === 'published' && blank($contract->published_at)) {
                $contract->published_at = now();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'fiscal_year' => 'integer',
            'official_budget' => 'decimal:2',
            'publication_date' => 'date',
            'offers_deadline_date' => 'date',
            'evaluation_date' => 'date',
            'award_date' => 'date',
            'published_at' => 'datetime',
        ];
    }

    public function contractType(): BelongsTo
    {
        return $this->belongsTo(ContractType::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ContractDocument::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ContractParticipant::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopePublished(Builder $query): void
    {
        $query
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public static function nextProcessCode(int $fiscalYear, ?int $ignoreId = null): string
    {
        $codes = static::query()
            ->withTrashed()
            ->where('fiscal_year', $fiscalYear)
            ->when($ignoreId !== null, fn (Builder $query): Builder => $query->whereKeyNot($ignoreId))
            ->pluck('process_code');

        $maxSequence = 0;

        foreach ($codes as $code) {
            if (! is_string($code)) {
                continue;
            }

            if (preg_match('/^FSE-(\d+)-'.$fiscalYear.'$/', $code, $matches) !== 1) {
                continue;
            }

            $maxSequence = max($maxSequence, (int) ($matches[1] ?? 0));
        }

        return sprintf('FSE-%03d-%d', $maxSequence + 1, $fiscalYear);
    }

    public function syncAwardedContractorFromParticipants(): void
    {
        $awardedParticipant = $this->participants()
            ->where('is_awarded', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        $attributes = [
            'contractor_name' => $awardedParticipant?->name,
            'contractor_nit' => $awardedParticipant?->nit,
            'contractor_social_object' => $awardedParticipant?->social_object,
        ];

        if (
            $this->contractor_name === $attributes['contractor_name']
            && $this->contractor_nit === $attributes['contractor_nit']
            && $this->contractor_social_object === $attributes['contractor_social_object']
        ) {
            return;
        }

        $this->forceFill($attributes)->saveQuietly();
    }

    /**
     * @return array<int, string>
     */
    public static function requiredDocumentTypesForProcessStatus(string $processStatus): array
    {
        return match ($processStatus) {
            'en_curso' => [
                'estudios_previos',
                'invitacion_pliegos',
                'formato_propuesta',
            ],
            'adjudicado' => [
                'estudios_previos',
                'invitacion_pliegos',
                'formato_propuesta',
                'acta_cierre',
                'informe_evaluacion',
                'acto_adjudicacion',
            ],
            default => [],
        };
    }
}
