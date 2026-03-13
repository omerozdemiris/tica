<?php

namespace App\Traits;

use App\Models\Cart;
use App\Models\Product;
use Carbon\Carbon;

trait HasPromotionLogic
{
    /**
     * Check if the promotion is valid for a given cart.
     */
    public function getPromotionError(Cart $cart, bool $checkPublic = false): ?string
    {
        if (!$this->is_active) {
            return 'Bu kupon şu anda aktif değil.';
        }

        if ($checkPublic && !$this->public) {
            return 'Bu kupon artık kullanılamamaktadır.';
        }

        // 1. Check conditions (Quota / Date)
        if ($this->condition_type == 1) { // Quota
            if (!is_null($this->usage_limit) && $this->usage_limit <= 0) {
                return 'Bu kuponun kullanım kotası dolmuştur.';
            }
        } elseif ($this->condition_type == 2) { // Date
            $now = Carbon::now();
            if ($this->start_date && $now->lt($this->start_date)) {
                return 'Bu kupon henüz aktifleşmemiştir.';
            }
            if ($this->end_date && $now->gt($this->end_date)) {
                return 'Bu kuponun kullanım süresi dolmuştur.';
            }
        }

        // 2. Check Application Area (Products / Categories / Cart Total)
        $data = $this->data ?? [];
        $selectionType = $data['type'] ?? 'products';
        $itemIds = $data['item_ids'] ?? [];

        if ($selectionType === 'cart_total') {
            if (!is_null($this->min_total) && $cart->total_price < $this->min_total) {
                return 'Bu kuponu kullanabilmek için minimum sepet tutarı ' . number_format($this->min_total, 2, ',', '.') . ' ₺ olmalıdır.';
            }
            return null; // Cart total requirement met, no need to check items
        }

        // Check if at least one assigned product or category is in cart
        if (empty($itemIds)) {
            return 'Bu kupon için geçerli ürün bulunamadı.';
        }

        $cartItems = $cart->items()->with('product.categories')->get();
        $found = false;

        foreach ($cartItems as $cartItem) {
            if ($selectionType === 'products') {
                if (in_array($cartItem->product_id, $itemIds)) {
                    $found = true;
                    break;
                }
            } elseif ($selectionType === 'categories') {
                $productCategories = $cartItem->product->categories->pluck('id')->toArray();
                if (!empty(array_intersect($productCategories, $itemIds))) {
                    $found = true;
                    break;
                }
            }
        }

        if (!$found) {
            return 'Sepetinizdeki ürünler bu kupon için geçerli değildir.';
        }

        return null;
    }

    /**
     * Check if the promotion is valid for a given cart.
     */
    public function isValidForCart(Cart $cart, bool $checkPublic = false): bool
    {
        return $this->getPromotionError($cart, $checkPublic) === null;
    }

    /**
     * Apply the promotion to the cart.
     */
    public function calculateDiscount(Cart $cart): float
    {
        $totalPrice = (float) $cart->items->sum('subtotal');
        return ($totalPrice * $this->discount_rate) / 100;
    }
}
