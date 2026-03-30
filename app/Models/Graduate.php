<?php

namespace App\Models;

use Database\Factories\GraduateFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Graduate extends Authenticatable
{
    /** @use HasFactory<GraduateFactory> */
    use HasFactory, Notifiable;

    public const STATUS_OPTIONS = [
        'preloaded' => 'Precargado',
        'active' => 'Activo',
        'blocked' => 'Bloqueado',
    ];

    public const VERIFICATION_STATUS_OPTIONS = [
        'pending' => 'Pendiente',
        'verified' => 'Verificado',
    ];

    protected $fillable = [
        'national_id',
        'full_name',
        'graduation_year',
        'email',
        'phone',
        'current_occupation',
        'city',
        'country',
        'data_processing_consent_at',
        'status',
        'password',
        'activated_at',
        'last_login_at',
        'academic_title',
        'graduation_date',
        'graduation_act_number',
        'graduation_folio',
        'record_verification_status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'graduation_year' => 'integer',
            'data_processing_consent_at' => 'datetime',
            'activated_at' => 'datetime',
            'last_login_at' => 'datetime',
            'graduation_date' => 'date',
        ];
    }

    public function documents(): HasMany
    {
        return $this->hasMany(GraduateDocument::class)->orderBy('sort_order')->orderBy('id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'active');
    }
}
