<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AreaPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'area_name',
        'responsible_teachers',
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
        ];
    }
}
