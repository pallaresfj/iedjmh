<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Contractor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'nit',
        'social_object',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ContractParticipant::class);
    }
}
