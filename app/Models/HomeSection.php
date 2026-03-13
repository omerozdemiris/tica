<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeSection extends Model
{
    protected $fillable = [
        'name',
        'title',
        'description',
        'is_active',
        'sort_order',
        'data',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'data' => 'array',
    ];

    public function getIsFixedAttribute(): bool
    {
        return in_array($this->name, ['slider', 'new_products', 'all_categories']);
    }
}
