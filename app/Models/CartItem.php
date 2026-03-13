<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_id',
        'product_attribute_term_id',
        'variant_ids',
        'quantity',
        'price',
        'subtotal',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'variant_ids' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (CartItem $item) {
            $item->subtotal = (float) $item->price * (int) $item->quantity;
        });

        static::saved(function (CartItem $item) {
            $item->cart?->recalculateTotals();
        });

        static::deleted(function (CartItem $item) {
            $item->cart?->recalculateTotals();
        });
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductAttributeTerm::class, 'product_attribute_term_id');
    }
    public function variants()
    {
        if (empty($this->variant_ids)) {
            return collect();
        }
        return ProductAttributeTerm::with(['attribute', 'term'])->whereIn('id', $this->variant_ids)->get();
    }
}
