<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaffMember extends Model
{
    use HasFactory, SoftDeletes;

    public const STAFF_GROUP_OPTIONS = [
        'directive' => 'Directivo',
        'teacher' => 'Docente',
        'administrative' => 'Administrativo',
        'support' => 'Apoyo',
    ];

    public const STATUS_OPTIONS = [
        'draft' => 'Borrador',
        'published' => 'Publicado',
        'archived' => 'Archivado',
    ];

    protected $fillable = [
        'full_name',
        'position_title',
        'department_label',
        'staff_group',
        'campus_id',
        'institutional_email',
        'phone',
        'profile_photo_path',
        'status',
        'published_at',
        'sort_order',
        'created_by',
        'updated_by',
    ];

    protected static function booted(): void
    {
        static::saving(function (StaffMember $staffMember): void {
            if ($staffMember->status === 'published' && blank($staffMember->published_at)) {
                $staffMember->published_at = now();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'sort_order' => 'integer',
        ];
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
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
}
