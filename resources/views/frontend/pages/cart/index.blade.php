@extends($theme->thene . '.layouts.app')
@php
    $cart = $data->cart;
    $items = $cart?->items ?? collect();
    $canCheckout = $items->count() > 0;
    $pricingData = is_array($pricing ?? null) ? $pricing : [];
    $pricingTotals = array_merge(
        [
            'net' => (float) ($cart?->total_price ?? 0),
            'tax' => 0.0,
            'gross' => (float) ($cart?->total_price ?? 0),
        ],
        $pricingData['totals'] ?? [],
    );
    $subtotal = $cart ? (float) $cart->total_price : 0;
    $taxAmount = 0;
    if ($cart && $store->tax_enabled && $store->tax_rate > 0) {
        $taxAmount = $subtotal * ($store->tax_rate / 100);
    }
    $baseWithTax = $subtotal + $taxAmount;
    $shippingCost = 0;
    if ($cart && $store->shipping_price_limit > 0 && $baseWithTax < $store->shipping_price_limit) {
        $shippingCost = (float) ($store->shipping_price ?? 0);
    }
    $pricingTotals['gross'] = $baseWithTax + $shippingCost;
    if ($cart && $cart->discount_amount > 0) {
        $pricingTotals['gross'] = (float) $cart->total_price;
        if (
            isset($pricingTotals['net']) &&
            isset($pricingData['totals']['gross']) &&
            $pricingData['totals']['gross'] > 0
        ) {
            $ratio = $cart->total_price / $pricingData['totals']['gross'];
            $pricingTotals['net'] = $pricingTotals['net'] * $ratio;
            $pricingTotals['tax'] = $pricingTotals['gross'] - $pricingTotals['net'];
        }
    }
    $taxBreakdown = $pricingData['tax_breakdown'] ?? [];
    $taxEnabled = (bool) ($pricingData['tax_enabled'] ?? false);
    $storeTaxRate = $pricingData['store_tax_rate'] ?? null;
    $storeTaxEnabled = $taxEnabled && $storeTaxRate !== null;
    $storeTaxRateLabel =
        $storeTaxRate !== null ? '%' . rtrim(rtrim(number_format($storeTaxRate, 2, ',', '.'), '0'), ',') : null;
    $exceptionItems = [];
    foreach ($items as $cartItem) {
        $pricingItem = $pricingData['items'][$cartItem->id] ?? null;
        if (!$pricingItem) {
            continue;
        }
        $behavior = $pricingItem['behavior'] ?? \App\Services\PricingService::TAX_BEHAVIOR_INHERIT;
        if ($behavior === \App\Services\PricingService::TAX_BEHAVIOR_INHERIT) {
            continue;
        }
        $exceptionItems[] = [
            'title' => Str::limit($cartItem->product?->title ?? 'Ürün', 20),
            'variant' => Str::limit($cartItem->variant?->term->name ?? null, 20),
            'behavior' => $behavior,
            'tax_rate' => $pricingItem['tax_rate'] ?? null,
            'tax' => $pricingItem['tax'] ?? 0,
            'net' => $pricingItem['net'] ?? 0,
            'gross' => $pricingItem['gross'] ?? 0,
        ];
    }
    $formatMoney = function ($value) {
        return number_format((float) $value, 2, ',', '.');
    };
@endphp
@section('title', 'Sepetiniz')
@section('breadcrumb_title', 'Sepetiniz')
@section('breadcrumb_actions')
    @if ($canCheckout)
        <a href="{{ route('cart.checkout') }}"
            class="py-3 bg-blue-600 text-white text-xs md:text-sm font-semibold hover:bg-blue-700 transition border border-gray-300/30">
            Satın Alma Adımlarına Geç
        </a>
    @endif
@endsection
@section('content')
    @include($theme->thene . '.parts.breadcrumb')
    <section class="py-10 md:py-12">
        <div class="max-w-[1680px] mx-auto grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8">
            <div class="lg:col-span-2 space-y-6">
                @forelse ($items as $item)
                    @php
                        $pricingItem = $pricingData['items'][$item->id] ?? null;
                        $quantity = max(1, $pricingItem['quantity'] ?? ($item->quantity ?? 1));
                        $unitNet = $pricingItem ? ($pricingItem['net'] ?? 0) / $quantity : null;
                        $unitTax = $pricingItem ? ($pricingItem['tax'] ?? 0) / $quantity : null;
                        $taxRate = $pricingItem['tax_rate'] ?? null;
                        $taxLabel =
                            $taxRate !== null
                                ? '%' . rtrim(rtrim(number_format($taxRate, 2, ',', '.'), '0'), ',')
                                : 'Muaf';
                    @endphp
                    <div
                        class="bg-white border border-gray-300/30 p-4 md:p-5 flex flex-col sm:flex-row gap-12 sm:items-center">
                        <div class="flex items-center gap-4 flex-1">
                            <div
                                class="w-24 h-24 bg-gray-50 border border-gray-300/30 flex items-center justify-center overflow-hidden">
                                @if ($item->product?->photo)
                                    <img src="{{ asset($item->product->photo) }}" alt="{{ $item->product?->title }}"
                                        class="w-full h-full object-cover">
                                @else
                                    <i class="ri-image-line text-gray-300 text-3xl"></i>
                                @endif
                            </div>
                            <div class="space-y-2">
                                <h3 class="text-sm font-semibold text-gray-900">{{ $item->product?->title ?? 'Ürün' }}</h3>
                                @if ($item->variant_ids)
                                    <div class="space-y-1">
                                        @foreach ($item->variants() as $variant)
                                            @php
                                                $term = $variant->term;
                                                $displayName = $term->name ?? '';
                                                $colorMatch =
                                                    isset($term->value) && str_starts_with($term->value, '#')
                                                        ? $term->value
                                                        : null;
                                            @endphp
                                            <p class="text-xs text-gray-500 flex items-center gap-1.5">
                                                <span
                                                    class="font-medium">{{ $variant->attribute?->name ?? 'Varyant' }}:</span>
                                                @if ($colorMatch)
                                                    <span class="w-3 h-3 border border-gray-300/30"
                                                        style="background-color: {{ $colorMatch }}"></span>
                                                @endif
                                                <span>{{ $displayName }}</span>
                                            </p>
                                        @endforeach
                                    </div>
                                @elseif ($item->variant)
                                    @php
                                        $termName = $item->variant->term?->name ?? '';
                                        $colorMatch = null;
                                        $displayName = $termName;
                                        if (preg_match('/#([a-fA-F0-9]{3}){1,2}/', $termName, $matches)) {
                                            $colorMatch = $matches[0];
                                            $displayName = trim(str_replace($colorMatch, '', $termName));
                                        }
                                    @endphp
                                    <p class="text-xs text-gray-500 flex items-center gap-1.5">
                                        <span
                                            class="font-medium">{{ $item->variant->attribute?->name ?? 'Varyant' }}:</span>
                                        @if ($colorMatch)
                                            <span class="w-3 h-3 border border-gray-300/30"
                                                style="background-color: {{ $colorMatch }}"></span>
                                        @endif
                                        <span>{{ $displayName }}</span>
                                    </p>
                                @endif
                                <div class="text-xs text-gray-400 space-y-1">
                                    <p>Birim Fiyat (KDV Dahil):
                                        {{ number_format((float) $item->price, 2, ',', '.') }} ₺
                                    </p>
                                    {{-- @if ($pricingItem)
                                        <p>Birim Vergisiz:
                                            {{ number_format((float) $unitNet, 2, ',', '.') }} ₺
                                            @if ($unitTax > 0)
                                                • KDV ({{ $taxLabel }}):
                                                {{ number_format((float) $unitTax, 2, ',', '.') }} ₺
                                            @else
                                                • KDV: Dahil
                                            @endif
                                        </p>
                                    @endif --}}
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <form action="{{ route('cart.update', $item->id) }}" method="POST"
                                class="flex items-center gap-2" data-cart-update-form>
                                @csrf
                                @method('PATCH')
                                <div class="flex items-center border border-gray-300/30 overflow-hidden">
                                    <button type="button" class="px-3 py-1 text-sm text-gray-600 decrement">-</button>
                                    <input type="number" name="quantity" value="{{ $item->quantity }}" min="0"
                                        class="w-14 text-center text-sm py-1 border-x border-gray-300/30 outline-none">
                                    <button type="button" class="px-3 py-1 text-sm text-gray-600 increment">+</button>
                                </div>
                                <button type="submit"
                                    class="px-3 py-1.5 text-xs font-semibold {{ $theme->color ? 'text-' . $theme->color : 'text-blue-600' }} border border-gray-300/30 hover:{{ $theme->color ? 'bg-' . $theme->color . '/10' : 'bg-blue-50' }} transition">
                                    Güncelle
                                </button>
                            </form>
                            <button type="button" data-cart-remove="{{ route('cart.destroy', $item->id) }}"
                                class="px-3 py-1.5 text-xs font-semibold {{ $theme->color ? 'text-' . $theme->color : 'text-red-600' }} border border-gray-300/30 hover:{{ $theme->color ? 'bg-' . $theme->color . '/10' : 'bg-red-50' }} transition">
                                Kaldır
                            </button>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Toplam</p>
                            <p
                                class="text-lg font-semibold {{ $theme->color ? 'text-' . $theme->color : 'text-gray-900' }}">
                                {{ number_format((float) $item->subtotal, 2, ',', '.') }} ₺
                            </p>
                        </div>
                    </div>
                @empty
                    <div
                        class="bg-white border border-gray-300/30 p-10 text-center {{ $theme->color ? 'text-' . $theme->color : 'text-gray-500' }}">
                        Sepetinizde ürün bulunmuyor.
                    </div>
                @endforelse
            </div>
            @if ($canCheckout)
                <div class="space-y-6">
                    <div class="bg-white border border-gray-300/30 p-5 md:p-6">
                        <h2
                            class="text-lg font-semibold {{ $theme->color ? 'text-' . $theme->color : 'text-gray-900' }} mb-4">
                            Sepet Özeti</h2>
                        <dl class="space-y-3 text-sm text-gray-500">
                            <div class="flex items-center justify-between">
                                <dt>Ürün Sayısı</dt>
                                <dd>{{ $cart?->total_items ?? 0 }}</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt>Ara Toplam {{ $taxEnabled ? '(KDV Hariç)' : '(KDV Dahil)' }}</dt>
                                <dd>{{ $formatMoney($pricingTotals['net'] ?? 0) }} ₺</dd>
                            </div>
                            @if ($cart && $cart->discount_amount > 0)
                                <div class="flex items-center justify-between text-green-600">
                                    <dt>İndirim ({{ $cart->appliedPromotion->code ?? '' }})</dt>
                                    <dd>- {{ $formatMoney($cart->discount_amount) }} ₺</dd>
                                </div>
                            @endif
                            @php
                                $currentBaseWithTax =
                                    $cart->total_price +
                                    ($store->tax_enabled && $store->tax_rate > 0
                                        ? $cart->total_price * ($store->tax_rate / 100)
                                        : 0);
                            @endphp
                            @if ($currentBaseWithTax < $store->shipping_price_limit && $store->shipping_price > 0)
                                <div class="flex items-center justify-between">
                                    <dt>Kargo Ücreti</dt>
                                    <dd>{{ $formatMoney($store->shipping_price) }} ₺</dd>
                                </div>
                            @endif
                            @if ($taxEnabled)
                                @if (!empty($taxBreakdown))
                                    @foreach ($taxBreakdown as $taxLine)
                                        <div class="flex items-center justify-between">
                                            <dt>KDV ({{ $taxLine['label'] ?? '' }})</dt>
                                            <dd>{{ $formatMoney($taxLine['amount'] ?? 0) }} ₺</dd>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="flex items-center justify-between">
                                        <dt>KDV</dt>
                                        <dd>{{ $formatMoney($pricingTotals['tax'] ?? 0) }} ₺</dd>
                                    </div>
                                @endif
                            @endif
                            <div
                                class="flex items-center justify-between font-semibold text-gray-900 pt-3 border-t border-gray-300/30">
                                <dt>Genel Toplam (KDV Dahil)</dt>
                                <dd>{{ $formatMoney($pricingTotals['gross'] ?? 0) }} ₺</dd>
                            </div>
                        </dl>
                        <p class="text-xs text-gray-500 mt-3">
                            @if ($storeTaxEnabled)
                                Tüm ürünler mağaza genel KDV oranı {{ $storeTaxRateLabel }} kapsamında
                                vergilendirilmektedir.
                            @else
                                Tüm ürünlere KDV dahildir.
                            @endif
                        </p>
                        <div
                            class="mt-12 mb-4 {{ $cart && $cart->applied_promotion_id ? 'bg-gray-50 opacity-60' : 'bg-white' }}">
                            <label class="text-xs font-semibold text-gray-500 mb-2 block">İndirim Kuponu</label>
                            <div class="flex">
                                <input type="text" id="coupon_code_input" placeholder="KODU GİRİN"
                                    @disabled($cart && $cart->applied_promotion_id)
                                    class="flex-1 px-3 py-2 border border-gray-300/30 border-r-0 outline-none text-xs md:text-sm">
                                <button type="button" id="apply_coupon_btn" @disabled($cart && $cart->applied_promotion_id)
                                    class="px-4 md:px-5 {{ $theme->color ? 'bg-' . $theme->color : 'bg-blue-600' }} text-white text-xs md:text-sm font-semibold hover:opacity-90 transition border border-gray-300/30">
                                    UYGULA
                                </button>
                            </div>
                            @if (isset($data->availablePromotions) && $data->availablePromotions->isNotEmpty())
                                <div class="flex flex-wrap gap-2 mt-3">
                                    @foreach ($data->availablePromotions as $promo)
                                        <button type="button"
                                            class="promotion-card flex items-center gap-2 px-2.5 py-1.5 border border-gray-300/30 {{ $theme->color ? 'text-' . $theme->color . ' hover:bg-' . $theme->color . ' hover:text-white' : 'text-gray-600 hover:bg-blue-600 hover:text-white' }} transition-all text-[11px] font-semibold {{ $cart && $cart->applied_promotion_id ? 'opacity-50 cursor-not-allowed' : '' }}"
                                            data-promotion-id="{{ $promo->id }}"
                                            data-promotion-code="{{ $promo->code }}"
                                            data-promotion-discount="{{ $promo->discount_rate }}"
                                            @disabled($cart && $cart->applied_promotion_id)>
                                            <i class="ri-gift-line"></i>
                                            <span class="font-bold">{{ $promo->code }}</span>
                                            <span class="text-[10px] opacity-75">(-%{{ $promo->discount_rate }})</span>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="mt-6 space-y-3">
                            @if ($canCheckout)
                                @if ((optional($store)->auth_required ?? false) && !auth()->check())
                                    <a href="{{ route('login') }}"
                                        class="block text-center flex items-center justify-center gap-4 w-full px-4 py-3 {{ $theme->color ? 'bg-' . $theme->color : 'bg-blue-600' }} text-white text-sm font-semibold hover:bg-gray-700 transition-colors border border-gray-300/30">
                                        <span><i class="ri-arrow-right-line font-light text-xl"></i></span>
                                        Giriş Yaparak Satın Al
                                    </a>
                                @else
                                    <a href="{{ route('cart.checkout') }}"
                                        class="block text-center flex items-center justify-center gap-4 w-full px-4 py-3 {{ $theme->color ? 'bg-' . $theme->color : 'bg-blue-600' }} text-white text-sm font-semibold hover:bg-gray-700 transition-colors border border-gray-300/30">
                                        <span><i class="ri-arrow-right-line font-light text-xl"></i></span>
                                        Satın Alma Aşamasına Geç
                                    </a>
                                @endif
                                <form action="{{ route('cart.clear') }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                        class="w-full text-center flex items-center justify-center gap-4 px-4 py-3 border border-gray-300/30 text-sm font-semibold {{ $theme->color ? 'text-' . $theme->color : 'text-gray-600' }} hover:bg-gray-100 transition-colors">
                                        <span><i class="ri-delete-bin-line font-light text-xl"></i></span>
                                        Sepeti Temizle
                                    </button>
                                </form>
                            @else
                                <p class="text-sm {{ $theme->color ? 'text-' . $theme->color : 'text-gray-500' }}">
                                    Sepetiniz boş. Hemen <a href="{{ route('products.index') }}"
                                        class="{{ $theme->color ? 'text-' . $theme->color : 'text-blue-600' }}">ürünleri</a>
                                    inceleyin.
                                </p>
                            @endif
                        </div>
                    </div>
                    <div
                        class="bg-white border border-gray-300/30 {{ $theme->color ? 'text-' . $theme->color : 'text-blue-800' }} p-5 text-sm">
                        <h3 class="text-sm font-semibold mb-2">Güvenilir Alışveriş</h3>
                        <p>Mağazamızda yapacağınız alışverişlerde 256-bit SSL sertifikası ve güvenli ödeme altyapısı
                            kullanılmaktadır.</p>
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
