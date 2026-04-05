<?php

namespace App\Models;

use Database\Factories\GraduateDocumentFactory;
use App\Rules\GoogleDriveUrl;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Throwable;

class GraduateDocument extends Model
{
    /** @use HasFactory<GraduateDocumentFactory> */
    use HasFactory;

    public const TYPE_OPTIONS = [
        'Académico' => 'Académico',
        'Personal' => 'Personal',
        'Institucional' => 'Institucional',
        'Otro' => 'Otro',
    ];

    protected $fillable = [
        'graduate_id',
        'title',
        'type_label',
        'description',
        'drive_url',
        'file_path',
        'file_disk',
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
            $document->drive_url = filled($document->drive_url) ? trim((string) $document->drive_url) : null;
            $document->file_path = filled($document->file_path) ? trim((string) $document->file_path) : null;
            $document->file_disk = filled($document->file_disk) ? trim((string) $document->file_disk) : 'local';

            Validator::make(
                [
                    'drive_url' => $document->drive_url,
                    'file_path' => $document->file_path,
                    'file_disk' => $document->file_disk,
                ],
                [
                    'drive_url' => ['nullable', 'string', 'max:2048', new GoogleDriveUrl],
                    'file_path' => ['nullable', 'string', 'max:2048'],
                    'file_disk' => ['required', 'string', 'max:40'],
                ],
                [],
                [
                    'drive_url' => 'URL de Google Drive',
                    'file_path' => 'archivo del documento',
                    'file_disk' => 'disco del archivo',
                ]
            )->after(function ($validator) use ($document): void {
                if (blank($document->drive_url) && blank($document->file_path)) {
                    $validator->errors()->add('drive_url', 'Debes registrar un enlace de Google Drive o subir un archivo.');
                }
            })->validate();
        });
    }

    public function getAccessUrlAttribute(): ?string
    {
        if (filled($this->drive_url)) {
            return $this->drive_url;
        }

        if (blank($this->file_path)) {
            return null;
        }

        $disk = $this->file_disk ?: 'local';

        try {
            if (! Storage::disk($disk)->exists($this->file_path)) {
                return null;
            }

            return route('egresados.panel.documentos.archivo', ['document' => $this]);
        } catch (Throwable) {
            return null;
        }
    }

    public function isGoogleDriveDocument(): bool
    {
        return filled($this->drive_url);
    }

    public function isFileDocument(): bool
    {
        return filled($this->file_path);
    }

    public function graduate(): BelongsTo
    {
        return $this->belongsTo(Graduate::class);
    }
}
