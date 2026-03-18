<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
        'sort_order',
        'parent_id',
        'created_by',
        'updated_by',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class);
    }

    public function procedures(): HasMany
    {
        return $this->hasMany(Procedure::class);
    }

    public function posts(): MorphToMany
    {
        return $this->morphedByMany(Post::class, 'categorizable')
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    public function events(): MorphToMany
    {
        return $this->morphedByMany(Event::class, 'categorizable')
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    public function documents(): MorphToMany
    {
        return $this->morphedByMany(Document::class, 'categorizable')
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    public function projects(): MorphToMany
    {
        return $this->morphedByMany(Project::class, 'categorizable')
            ->withPivot('sort_order')
            ->withTimestamps();
    }
}
