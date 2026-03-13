<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductAttributeTerm;
use App\Models\Store;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class CartService
{
    protected ?Store $store;

    public function __construct()
    {
        $this->store = Store::first();
    }

    /**
     * Retrieve the current cart for the visitor.
     */
    public function getCart(bool $create = true): ?Cart
    {
        [$userId, $guestId] = $this->identifiers($create);

        if (!$userId && !$guestId) {
            return null;
        }

        $cart = Cart::with(['items.product', 'items.variant'])
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->when(!$userId && $guestId, fn($q) => $q->where('guest_id', $guestId))
            ->first();

        if (!$cart && $userId && $guestId) {
            $cart = Cart::with(['items.product', 'items.variant'])
                ->where('guest_id', $guestId)
                ->first();
            if ($cart) {
                $cart->user_id = $userId;
                $cart->guest_id = null;
                $cart->save();
                $cart->refresh();
            }
        }

        if (!$cart && $create) {
            $cart = Cart::create([
                'user_id' => $userId,
                'guest_id' => $guestId,
                'total_price' => 0,
                'total_items' => 0,
            ]);
        }

        if ($userId && $guestId && $cart && $cart->guest_id === $guestId && $cart->user_id !== $userId) {
            $cart->user_id = $userId;
            $cart->guest_id = null;
            $cart->save();
            $cart->refresh();
        }

        return $cart?->loadMissing(['items.product', 'items.variant']);
    }

    public function getSummary(): array
    {
        $cart = $this->getCart(false);

        if (!$cart) {
            return [
                'count' => 0,
                'total' => 0.0,
            ];
        }

        return [
            'count' => (int) $cart->total_items,
            'total' => (float) $cart->total_price,
        ];
    }

    /**
     * @throws ValidationException
     */
    public function addProduct(Product $product, int $quantity = 1, array $variantIds = []): Cart
    {
        $this->ensurePurchasable($product);

        if ($quantity < 1) {
            $quantity = 1;
        }

        $cart = DB::transaction(function () use ($product, $variantIds, $quantity) {
            $cart = $this->getCart();

            $lockedProduct = Product::query()->lockForUpdate()->find($product->id);
            if (!$lockedProduct) {
                throw ValidationException::withMessages([
                    'product' => 'Ürün bulunamadı.',
                ]);
            }

            // Varyasyonları ID sırasına göre alalım (eşleştirme tutarlılığı için)
            sort($variantIds);
            $variants = collect();
            
            // Ürünün tüm zorunlu niteliklerini (attribute) bulalım
            $requiredAttributeIds = ProductAttributeTerm::query()
                ->where('product_id', $lockedProduct->id)
                ->distinct()
                ->pluck('attribute_id');

            if (!empty($variantIds)) {
                $variants = ProductAttributeTerm::query()
                    ->whereIn('id', $variantIds)
                    ->where('product_id', $lockedProduct->id)
                    ->lockForUpdate()
                    ->get();

                if ($variants->count() !== count($variantIds)) {
                    throw ValidationException::withMessages([
                        'variant' => 'Bazı seçili varyasyonlar bu ürüne ait değil.',
                    ]);
                }
            }

            // Tüm niteliklerin seçilip seçilmediğini kontrol et
            if ($requiredAttributeIds->isNotEmpty()) {
                $selectedAttributeIds = $variants->pluck('attribute_id')->unique();
                
                if ($selectedAttributeIds->count() < $requiredAttributeIds->count()) {
                    throw ValidationException::withMessages([
                        'variant' => 'Lütfen tüm ürün seçeneklerini seçiniz.',
                    ]);
                }
            }

            // Fiyat hesaplama
            $price = $lockedProduct->price;
            if (
                !is_null($lockedProduct->discount_price) &&
                $lockedProduct->discount_price > 0 &&
                $lockedProduct->discount_price < $lockedProduct->price
            ) {
                $price = $lockedProduct->discount_price;
            }

            // Varyasyon fiyat mantığı
            foreach ($variants as $v) {
                if ($v->price !== null && $v->price > 0) {
                    $price = $v->price; // Son bulunan fiyat geçerli olur
                }
            }

            $price = (float) $price;

            if ($price <= 0) {
                throw ValidationException::withMessages([
                    'product' => 'Bu ürün için fiyat tanımlanmamış.',
                ]);
            }

            $cartItemQuery = $cart->items()->where('product_id', $lockedProduct->id);

            if (!empty($variantIds)) {
                $cartItemQuery->whereJsonContains('variant_ids', $variantIds)
                             ->where(function($q) use ($variantIds) {
                                 $q->whereRaw('JSON_LENGTH(variant_ids) = ?', [count($variantIds)]);
                             });
            } else {
                $cartItemQuery->whereNull('variant_ids')
                              ->whereNull('product_attribute_term_id');
            }

            $cartItem = $cartItemQuery->lockForUpdate()->first();

            $newQuantity = ($cartItem?->quantity ?? 0) + $quantity;
            $this->ensureStockAvailable($lockedProduct, $variants, $newQuantity);

            if ($cartItem) {
                $cartItem->update([
                    'quantity' => $newQuantity,
                    'price' => $price,
                    'subtotal' => $newQuantity * $price,
                ]);
            } else {
                $cartItem = $cart->items()->create([
                    'product_id' => $lockedProduct->id,
                    'product_attribute_term_id' => !empty($variantIds) ? $variantIds[0] : null,
                    'variant_ids' => !empty($variantIds) ? $variantIds : null,
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $price * $quantity,
                ]);
            }

            $cart->refresh();
            $cart->recalculateTotals();

            return $cart;
        });

        return $cart->fresh(['items.product', 'items.variant']);
    }

    /**
     * @throws ValidationException
     */
    public function updateItem(CartItem $item, int $quantity): ?Cart
    {
        return DB::transaction(function () use ($item, $quantity) {
            $cart = $this->getCart(false);
            if (!$cart) {
                throw ValidationException::withMessages([
                    'cart' => 'Sepet bulunamadı.',
                ]);
            }

            $lockedItem = CartItem::query()->lockForUpdate()->find($item->id);
            if (!$lockedItem || $lockedItem->cart_id !== $cart->id) {
                throw ValidationException::withMessages([
                    'cart' => 'Sepet ögesi bulunamadı.',
                ]);
            }

            if ($quantity < 1) {
                $lockedItem->delete();
            } else {
                $product = Product::query()->lockForUpdate()->find($lockedItem->product_id);
                if (!$product) {
                    throw ValidationException::withMessages([
                        'product' => 'Ürün bulunamadı.',
                    ]);
                }

                $variants = collect();
                if ($lockedItem->variant_ids) {
                    $variants = ProductAttributeTerm::query()
                        ->whereIn('id', $lockedItem->variant_ids)
                        ->lockForUpdate()
                        ->get();
                } elseif ($lockedItem->product_attribute_term_id) {
                    $v = ProductAttributeTerm::query()->lockForUpdate()->find($lockedItem->product_attribute_term_id);
                    if ($v) $variants->push($v);
                }

                $this->ensureStockAvailable($product, $variants, $quantity);

                $lockedItem->quantity = $quantity;
                $lockedItem->save();
            }

            $cart->refresh();
            if (!$cart->items()->exists()) {
                $cart->delete();
                return null;
            }

            $cart->recalculateTotals();

            return $cart->fresh(['items.product', 'items.variant']);
        });
    }

    public function removeItem(CartItem $item): void
    {
        $cart = $this->getCart(false);
        if ($cart && $item->cart_id === $cart->id) {
            $item->delete();
            $cart->refresh();
            if (!$cart->items()->exists()) {
                $cart->delete();
            } else {
                $cart->recalculateTotals();
            }
        }
    }

    public function clear(): void
    {
        $cart = $this->getCart(false);
        if ($cart) {
            $cart->items()->delete();
            $cart->delete();
        }
    }

    protected function ensurePurchasable(Product $product): void
    {
        if (!$this->store || !$this->store->sell_enabled) {
            throw ValidationException::withMessages([
                'store' => 'Satın alma işlemi şu anda kapalı.',
            ]);
        }

        if (!$product->is_active) {
            throw ValidationException::withMessages([
                'product' => 'Bu ürün şu anda satışta değil.',
            ]);
        }
    }

    /**
     * Transfer guest cart to user after login/register
     */
    public function transferGuestCartToUser(?string $guestId, int $userId): void
    {
        if (!$guestId) {
            return;
        }

        $guestCart = Cart::with(['items.product', 'items.variant'])
            ->where('guest_id', $guestId)
            ->whereNull('user_id')
            ->first();

        if (!$guestCart) {
            return;
        }

        $userCart = Cart::with(['items.product', 'items.variant'])
            ->where('user_id', $userId)
            ->first();

        if ($userCart) {
            foreach ($guestCart->items as $guestItem) {
                $existingItemQuery = $userCart->items()
                    ->where('product_id', $guestItem->product_id);
                
                if ($guestItem->variant_ids) {
                    $existingItemQuery->whereJsonContains('variant_ids', $guestItem->variant_ids)
                                     ->whereRaw('JSON_LENGTH(variant_ids) = ?', [count($guestItem->variant_ids)]);
                } else {
                    $existingItemQuery->whereNull('variant_ids')
                                     ->whereNull('product_attribute_term_id');
                }

                $existingItem = $existingItemQuery->first();

                if ($existingItem) {
                    $existingItem->quantity += $guestItem->quantity;
                    $existingItem->subtotal = $existingItem->quantity * $existingItem->price;
                    $existingItem->save();
                } else {
                    $guestItem->cart_id = $userCart->id;
                    $guestItem->save();
                }
            }

            $userCart->refresh();
            $userCart->recalculateTotals();
            $guestCart->items()->delete();
            $guestCart->delete();
        } else {
            $guestCart->user_id = $userId;
            $guestCart->guest_id = null;
            $guestCart->save();
        }

        Session::forget('guest_cart_id');
    }

    /**
     * @return array{0:int|null,1:string|null}
     */
    protected function identifiers(bool $ensureGuestId = true): array
    {
        $userId = Auth::id();
        $guestId = Session::get('guest_cart_id');

        if (!$userId && !$guestId && $ensureGuestId) {
            $guestId = Session::getId();
            Session::put('guest_cart_id', $guestId);
        }

        return [$userId, $guestId];
    }

    public function currentGuestId(bool $ensure = true): ?string
    {
        return $this->identifiers($ensure)[1];
    }

    public function currentUserId(): ?int
    {
        return $this->identifiers(false)[0];
    }

    /**
     * @throws ValidationException
     */
    protected function ensureStockAvailable(Product $product, $variants, int $desiredQuantity): void
    {
        if (!$this->autoStockEnabled()) {
            return;
        }

        $variants = is_array($variants) ? collect($variants) : (is_null($variants) ? collect() : ( $variants instanceof \Illuminate\Support\Collection ? $variants : collect([$variants])));

        if ($variants->isNotEmpty()) {
            foreach ($variants as $variant) {
                $available = $this->availableStock($product, $variant);
                if ($available !== null && $desiredQuantity > $available) {
                    throw ValidationException::withMessages([
                        'stock' => $this->stockErrorMessage($product, $variant, $available),
                    ]);
                }
            }
        } else {
            $available = $this->availableStock($product, null);
            if ($available !== null && $desiredQuantity > $available) {
                throw ValidationException::withMessages([
                    'stock' => $this->stockErrorMessage($product, null, $available),
                ]);
            }
        }
    }

    protected function availableStock(Product $product, ?ProductAttributeTerm $variant): ?int
    {
        if (!$this->autoStockEnabled()) {
            return null;
        }

        if ($variant) {
            if ($this->isUnlimitedStock($variant->stock_type, $variant->stock)) {
                return null;
            }
            return max(0, (int) ($variant->stock ?? 0));
        }

        if ($this->isUnlimitedStock($product->stock_type, $product->stock)) {
            return null;
        }
        return max(0, (int) ($product->stock ?? 0));
    }

    protected function isUnlimitedStock(?int $stockType, ?int $stock): bool
    {
        return (int) ($stockType ?? 0) === 1 || $stock === null;
    }

    protected function stockErrorMessage(Product $product, ?ProductAttributeTerm $variant, int $available): string
    {
        $name = $product->title ?? 'Ürün';

        if ($variant) {
            $variant->loadMissing('term');
            if ($variant->term?->name) {
                $name .= ' - ' . $variant->term->name;
            }
        }

        if ($available <= 0) {
            return "{$name} stokta bulunmuyor.";
        }

        return "{$name} için en fazla {$available} adet ekleyebilirsiniz.";
    }

    protected function autoStockEnabled(): bool
    {
        return (bool) ($this->store?->auto_stock);
    }
}
