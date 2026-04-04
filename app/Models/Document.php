<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'summary',
        'description',
        'external_url',
        'document_number',
        'document_date',
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
            'document_date' => 'date',
            'published_at' => 'datetime',
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
