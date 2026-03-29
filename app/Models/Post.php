<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    public const MAX_PUBLISHED_FEATURED = 3;

    protected static function booted(): void
    {
        static::saved(function (Post $post): void {
            static::normalizePublishedFeaturedPosts($post);
        });
    }

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'status',
        'published_at',
        'sort_order',
        'is_featured',
        'cover_image_path',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
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

    private static function normalizePublishedFeaturedPosts(Post $post): void
    {
        if (! $post->is_featured || $post->status !== 'published') {
            return;
        }

        $maxOtherFeatured = max(0, self::MAX_PUBLISHED_FEATURED - 1);

        $otherFeaturedQuery = static::query()
            ->whereKeyNot($post->getKey())
            ->where('status', 'published')
            ->where('is_featured', true)
            ->orderBy('published_at')
            ->orderBy('id');

        $excessCount = $otherFeaturedQuery->count() - $maxOtherFeatured;

        if ($excessCount <= 0) {
            return;
        }

        $idsToUnfeature = $otherFeaturedQuery
            ->limit($excessCount)
            ->pluck('id')
            ->all();

        if ($idsToUnfeature === []) {
            return;
        }

        static::query()
            ->whereIn('id', $idsToUnfeature)
            ->update(['is_featured' => false]);
    }
}
