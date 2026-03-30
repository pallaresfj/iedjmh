<?php

namespace App\Models;

use Database\Factories\GraduateDocumentFactory;
use App\Rules\GoogleDriveUrl;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Validator;

class GraduateDocument extends Model
{
    /** @use HasFactory<GraduateDocumentFactory> */
    use HasFactory;

    protected $fillable = [
        'graduate_id',
        'title',
        'type_label',
        'description',
        'drive_url',
        'is_official',
        'is_visible',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_official' => 'boolean',
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (GraduateDocument $document): void {
            Validator::make(
                ['drive_url' => $document->drive_url],
                ['drive_url' => [new GoogleDriveUrl]]
            )->validate();
        });
    }

    public function graduate(): BelongsTo
    {
        return $this->belongsTo(Graduate::class);
    }
}
