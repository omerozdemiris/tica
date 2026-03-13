<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'code',
        'title',
        'description',
        'price',
        'discount_price',
        'tax_behavior',
        'tax_rate',
        'stock',
        'stock_type',
        'meta_title',
        'meta_description',
        'is_active',
        'click_count',
        'photo',
    ];

    public function getSlugAttribute(): string
    {
        return Str::slug($this->title);
    }
    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'tax_behavior' => 'integer',
        'tax_rate' => 'decimal:4',
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)->withTimestamps();
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductAttributeTerm::class);
    }

    public function isNew(): bool
    {
        return $this->created_at > now()->subDays(7);
    }

    public function getStockLabelAttribute(): string
    {
        if ($this->variants()->exists()) {
            if ($this->variants()->where('stock_type', 1)->exists()) {
                return 'Sınırsız';
            }
            $total = $this->variants()->sum('stock');
            return (string)$total;
        }

        if ($this->stock_type === 1) {
            return 'Sınırsız';
        }

        return $this->stock === null ? '0' : (string)$this->stock;
    }

    public function getPriceLabelAttribute(): string
    {
        if ($this->price === null) return '-';
        return number_format((float)$this->price, 2, ',', '.');
    }

    public function getCategoriesLabelAttribute(): string
    {
        if (!$this->relationLoaded('categories')) {
            $this->load('categories');
        }
        return $this->categories->pluck('name')->implode(', ');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function getSoldCountAttribute(): int
    {
        return (int) $this->orderItems()->sum('quantity');
    }

    public function getInCartCountAttribute(): int
    {
        return (int) $this->cartItems()->sum('quantity');
    }

    public function gallery()
    {
        return $this->hasMany(Document::class, 'parent', 'id')
            ->where('type', 1)
            ->orderBy('queue');
    }
}
