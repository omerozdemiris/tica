<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasPromotionLogic;

class Promotion extends Model
{
    use HasPromotionLogic;

    protected $fillable = [
        'code',
        'discount_rate',
        'condition_type',
        'usage_limit',
        'usage_count',
        'start_date',
        'end_date',
        'min_total',
        'is_active',
        'data',
        'public'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'public' => 'boolean',
        'data' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'condition_type' => 'integer',
        'usage_limit' => 'integer',
        'usage_count' => 'integer',
        'discount_rate' => 'integer',
        'min_total' => 'decimal:2',
    ];
}
