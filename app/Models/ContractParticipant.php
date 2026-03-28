<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'contractor_id',
        'name',
        'nit',
        'social_object',
        'evaluation_score',
        'is_awarded',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'evaluation_score' => 'decimal:2',
            'is_awarded' => 'boolean',
            'sort_order' => 'integer',
            'contractor_id' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (ContractParticipant $participant): void {
            $participant->syncContractAwardSnapshot();
        });

        static::deleted(function (ContractParticipant $participant): void {
            $participant->syncContractAwardSnapshot();
        });
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(Contractor::class);
    }

    private function syncContractAwardSnapshot(): void
    {
        if (! filled($this->contract_id)) {
            return;
        }

        Contract::query()
            ->whereKey($this->contract_id)
            ->first()?->syncAwardedContractorFromParticipants();
    }
}
