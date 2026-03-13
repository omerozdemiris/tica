<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductAttributeTerm;
use App\Models\Store;
use App\Models\Promotion;
use App\Services\CartService;
use App\Services\PricingService;
use Illuminate\Http\JsonResponse;
use App\Models\Theme;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected PricingService $pricingService
    ) {
        parent::__construct();
    }

    public function index(): View
    {
        $theme = Theme::first();
        $cart = $this->cartService->getCart(false);
        $store = Store::first();

        $pricing = $this->pricingService->summarizeCart($cart);

        $availablePromotions = [];
        if ($cart) {
            $availablePromotions = Promotion::where('is_active', true)
                ->where('public', true)
                ->get()
                ->filter(function ($promo) use ($cart) {
                    return $promo->isValidForCart($cart);
                });
        }

        $meta = (object) [
            'title' => ($store->meta_title ?? config('app.name')) . ' | Sepetiniz',
            'description' => $store->meta_description ?? null,
        ];

        $data = (object) [
            'cart' => $cart,
            'meta' => $meta,
            'availablePromotions' => $availablePromotions,
        ];

        return view($theme->thene . '.pages.cart.index', [
            'data' => $data,
            'meta' => $meta,
            'pricing' => $pricing,
        ]);
    }

    public function applyPromotion(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'promotion_id' => ['nullable', 'exists:promotions,id'],
            'code' => ['nullable', 'string'],
        ]);

        $cart = $this->cartService->getCart(false);
        if (!$cart) {
            return response()->json(['msg' => 'Sepetiniz boş.', 'code' => 2], 422);
        }

        if ($cart->applied_promotion_id) {
            return response()->json(['msg' => 'Sepetinizde zaten aktif bir kupon bulunuyor.', 'code' => 2], 422);
        }

        if (!empty($validated['promotion_id'])) {
            $promotion = Promotion::findOrFail($validated['promotion_id']);
        } elseif (!empty($validated['code'])) {
            $promotion = Promotion::where('code', $validated['code'])->first();
            if (!$promotion) {
                return response()->json(['msg' => 'Kupon bulunamadı.', 'code' => 2], 422);
            }
        } else {
            return response()->json(['msg' => 'Lütfen bir kupon seçin veya kod girin.', 'code' => 2], 422);
        }

        $error = $promotion->getPromotionError($cart);
        if ($error) {
            return response()->json(['msg' => $error, 'code' => 2], 422);
        }

        // For manual code entry, we might want to return info first for confirmation
        if ($request->has('check_only')) {
            $discount = $promotion->calculateDiscount($cart);
            return response()->json([
                'code' => 1,
                'promotion' => [
                    'id' => $promotion->id,
                    'code' => $promotion->code,
                    'discount_rate' => $promotion->discount_rate,
                    'discount_amount' => $discount,
                    'new_total' => max(0, $cart->total_price - $discount)
                ]
            ]);
        }

        DB::beginTransaction();
        try {
            $cart->applied_promotion_id = $promotion->id;
            $cart->recalculateTotals();

            if ($promotion->condition_type == 1) {
                $promotion->decrement('usage_limit');
                $promotion->increment('usage_count');
            } else {
                $promotion->increment('usage_count');
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['msg' => 'Kupon uygulanırken bir hata oluştu.', 'code' => 2], 500);
        }

        return response()->json([
            'msg' => 'Kupon başarıyla uygulandı.',
            'code' => 1
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        if ($request->has('variant_ids')) {
            $request->merge([
                'variant_ids' => array_values(array_filter((array) $request->input('variant_ids'), fn($v) => $v !== '' && $v !== null)),
            ]);
        }

        $rules = [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'variant_id' => ['nullable', 'integer', 'exists:product_attribute_term,id'],
            'variant_ids' => ['nullable', 'array'],
            'variant_ids.*' => ['integer', 'exists:product_attribute_term,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ];

        $messages = [
            'variant_ids.required' => 'Lütfen tüm ürün özelliklerini seçin.',
            'variant_ids.*.integer' => 'Lütfen tüm ürün özelliklerini seçin.',
            'variant_ids.*.exists' => 'Seçilen özellik geçerli değil.',
            'product_id.required' => 'Ürün bilgisi eksik.',
            'product_id.exists' => 'Ürün bulunamadı.',
            'quantity.integer' => 'Adet sayısı geçerli değil.',
            'quantity.min' => 'En az 1 adet seçmelisiniz.',
        ];

        $validated = $request->validate($rules, $messages);
        $product = Product::where('is_active', true)->findOrFail($validated['product_id']);

        $variantIds = $validated['variant_ids'] ?? [];
        if (empty($variantIds) && !empty($validated['variant_id'])) {
            $variantIds = [$validated['variant_id']];
        }

        if ($product->variants()->count() > 0 && empty($variantIds)) {
            throw ValidationException::withMessages([
                'variant_ids' => 'Lütfen tüm ürün özelliklerini seçin.',
            ]);
        }

        try {
            $cart = $this->cartService->addProduct(
                $product,
                $validated['quantity'] ?? 1,
                $variantIds
            );
        } catch (ValidationException $e) {
            return $this->errorResponse($request, $e->errors());
        }

        return $this->successResponse($request, $cart, 'Ürün sepete eklendi.');
    }

    public function update(Request $request, CartItem $item): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        try {
            $cart = $this->cartService->updateItem($item, $validated['quantity']);
        } catch (ValidationException $e) {
            return $this->errorResponse($request, $e->errors());
        }

        return $this->successResponse($request, $cart, 'Sepet güncellendi.');
    }

    public function destroy(Request $request, CartItem $item): RedirectResponse|JsonResponse
    {
        $this->cartService->removeItem($item);
        $cart = $this->cartService->getCart(false);
        return $this->successResponse($request, $cart, 'Ürün sepetten kaldırıldı.');
    }

    public function clear(Request $request): RedirectResponse|JsonResponse
    {
        $this->cartService->clear();
        $cart = $this->cartService->getCart(false);
        return $this->successResponse($request, $cart, 'Sepet temizlendi.');
    }

    protected function successResponse(Request $request, ?object $cart, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'code' => 1,
                'msg' => $message,
                'data' => [
                    'cart' => $cart,
                    'summary' => $this->cartService->getSummary(),
                    'pricing' => $this->pricingService->summarizeCart($cart),
                ],
            ]);
        }

        return back()->with('status', $message);
    }

    protected function errorResponse(Request $request, array $errors)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'code' => 0,
                'msg' => 'Bir hata oluştu',
                'errors' => $errors,
            ], 422);
        }

        return back()->withErrors($errors)->withInput();
    }
}
