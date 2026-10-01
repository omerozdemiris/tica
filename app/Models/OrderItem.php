<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_attribute_term_id',
        'variant_ids',
        'quantity',
        'price',
        'total',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'total' => 'decimal:2',
        'variant_ids' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
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
