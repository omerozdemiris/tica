<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAttributeTerm extends Model
{
    protected $table = 'product_attribute_term';

    protected $fillable = [
        'product_id',
        'attribute_id',
        'term_id',
        'price',
        'discount_price',
        'tax_behavior',
        'tax_rate',
        'stock',
        'stock_type',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'tax_behavior' => 'integer',
        'tax_rate' => 'decimal:4',
        'stock' => 'integer',
        'stock_type' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function getStockLabelAttribute(): string
    {
        return $this->stock === null ? 'Sınırsız' : (string)$this->stock;
    }
}


