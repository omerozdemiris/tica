<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'title',
        'text',
        'stock',
        'price',
        'context_ids',
        'context_type',
        'type',
        'read_at',
    ];

    protected $casts = [
        'context_ids' => 'json',
        'read_at' => 'datetime',
        'price' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
