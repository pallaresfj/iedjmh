<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AreaPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'area_name',
        'icon',
        'plan_url',
        'status',
        'sort_order',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'sort_order' => 'integer',
        ];
    }

    public function responsibleTeachers(): BelongsToMany
    {
        return $this->belongsToMany(StaffMember::class, 'area_plan_staff_member')
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderByPivot('sort_order')
            ->orderBy('full_name');
    }
}
