<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Bank extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'bank_name',
        'bank_iban',
        'bank_receiver',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
