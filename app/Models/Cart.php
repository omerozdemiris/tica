<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'guest_id',
        'total_price',
        'total_items',
        'applied_promotion_id',
        'discount_amount',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
    ];

    public function appliedPromotion()
    {
        return $this->belongsTo(Promotion::class, 'applied_promotion_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function recalculateTotals(): void
    {
        $this->loadMissing(['items', 'appliedPromotion']);

        $totalItems = $this->items->sum('quantity');
        $subtotal = $this->items->sum(function (CartItem $item) {
            return $item->subtotal;
        });

        $discountAmount = 0;
        if ($this->appliedPromotion) {
            if ($this->appliedPromotion->isValidForCart($this)) {
                $discountAmount = $this->appliedPromotion->calculateDiscount($this);
            } else {
                $this->applied_promotion_id = null;
            }
        }

        $this->total_items = $totalItems;
        $this->discount_amount = $discountAmount;
        $this->total_price = max(0, $subtotal - $discountAmount);
        $this->save();
    }

    protected static function emptySummary(): object
    {
        return (object) ['count' => 0, 'total' => 0.0];
    }

    public static function summaryForUser(?int $userId): object
    {
        if (!$userId) {
            return static::emptySummary();
        }

        $cart = static::where('user_id', $userId)->first();
        if (!$cart) return static::emptySummary();

        return (object) [
            'count' => (int) $cart->total_items,
            'total' => (float) $cart->total_price,
        ];
    }

    public static function summaryForGuest(?string $guestId): object
    {
        if (!$guestId) {
            return static::emptySummary();
        }

        $cart = static::whereNull('user_id')
            ->where('guest_id', $guestId)
            ->first();
        if (!$cart) return static::emptySummary();

        return (object) [
            'count' => (int) $cart->total_items,
            'total' => (float) $cart->total_price,
        ];
    }

    public static function headerSummary(?int $userId, ?string $guestId): object
    {
        if ($userId) {
            return static::summaryForUser($userId);
        }

        if ($guestId) {
            return static::summaryForGuest($guestId);
        }

        return static::emptySummary();
    }
}
