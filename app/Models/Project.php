<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::saving(function (Project $project): void {
            $otherProjectsExist = static::query()
                ->when($project->exists, fn ($query) => $query->whereKeyNot($project->getKey()))
                ->exists();

            if (! $otherProjectsExist) {
                $project->is_featured = true;
            }
        });

        static::saved(function (Project $project): void {
            if (! $project->is_featured || $project->status !== 'published') {
                return;
            }

            static::query()
                ->whereKeyNot($project->getKey())
                ->where('status', 'published')
                ->where('is_featured', true)
                ->update(['is_featured' => false]);
        });
    }

    protected $fillable = [
        'title',
        'slug',
        'summary',
        'description',
        'starts_on',
        'ends_on',
        'is_featured',
        'cover_image_path',
        'external_url',
        'gallery_image_paths',
        'status',
        'published_at',
        'sort_order',
        'seo_title',
        'seo_description',
        'seo_image_path',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
            'gallery_image_paths' => 'array',
        ];
    }

    public function categories(): MorphToMany
    {
        return $this->morphToMany(Category::class, 'categorizable')
            ->withPivot('sort_order')
            ->withTimestamps();
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
