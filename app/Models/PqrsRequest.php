<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class PqrsRequest extends Model
{
    use HasFactory, Notifiable;

    public function routeNotificationForMail(): ?string
    {
        return $this->applicant_email;
    }

    protected $fillable = [
        'tracking_code',
        'type',
        'is_anonymous',
        'status',
        'priority',
        'attachment_path',
        'message',
        'applicant_name',
        'applicant_email',
        'applicant_phone',
        'applicant_document',
        'applicant_address',
        'consent_habeas_data',
        'submitted_at',
        'resolved_at',
        'assigned_to',
        'internal_notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_anonymous' => 'boolean',
            'consent_habeas_data' => 'boolean',
            'submitted_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function messages(): HasMany
    {
        return $this->hasMany(PqrsMessage::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
