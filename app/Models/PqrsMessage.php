<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PqrsMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'pqrs_request_id',
        'user_id',
        'author_name',
        'author_email',
        'subject',
        'message',
        'responded_at',
        'is_internal',
        'attachments',
    ];

    protected function casts(): array
    {
        return [
            'responded_at' => 'datetime',
            'is_internal' => 'boolean',
            'attachments' => 'array',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(PqrsRequest::class, 'pqrs_request_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
