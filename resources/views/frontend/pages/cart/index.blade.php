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
            class="px-5 py-3 rounded-full bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition">
            Satın Alma Adımlarına Geç
        </a>
    @endif
@endsection
@section('content')
    @include($theme->thene . '.parts.breadcrumb')
    <section class="py-12">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-3 gap-8">
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

                        $currencySymbol = '₺';
                        $displayUnitPrice = (float) ($item->price ?? 0);
                        $displayLineTotal = $displayUnitPrice * (int) ($item->quantity ?? 1);
                    @endphp
                    <div
                        class="bg-white border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} rounded-2xl p-6 shadow-sm flex flex-col sm:flex-row gap-4 sm:items-center">
                        <div class="flex items-center gap-4 flex-1">
                            <div
                                class="w-24 h-24 rounded-xl bg-gray-50 border border-gray-100 flex items-center justify-center overflow-hidden">
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
                                                $file = $term->file ?? null;
                                            @endphp
                                            <p class="text-xs text-gray-500 flex items-center gap-1.5">
                                                <span
                                                    class="font-medium">{{ $variant->attribute?->name ?? 'Varyant' }}:</span>
                                                @if ($colorMatch)
                                                    <span class="w-3 h-3 rounded-full border border-gray-200"
                                                        style="background-color: {{ $colorMatch }}"></span>
                                                @elseif ($file)
                                                    <img src="{{ asset($file) }}" alt="{{ $displayName }}"
                                                        class="w-8 h-3 rounded-md object-cover relative">
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
                                            <span class="w-3 h-3 rounded-full border border-gray-200"
                                                style="background-color: {{ $colorMatch }}"></span>
                                        @endif
                                        <span>{{ $displayName }}</span>
                                    </p>
                                @endif
                                <div class="text-xs text-gray-400 space-y-1">
                                    <p>Birim Fiyat (KDV Dahil):
                                        {{ number_format((float) $displayUnitPrice, 2, ',', '.') }}
                                        {{ $currencySymbol }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <form action="{{ route('cart.update', $item->id) }}" method="POST"
                                class="flex items-center gap-2" data-cart-update-form>
                                @csrf
                                @method('PATCH')
                                <div class="flex items-center border border-gray-200 rounded-full overflow-hidden">
                                    <button type="button" class="px-3 py-1 text-sm text-gray-600 decrement">-</button>
                                    <input type="number" name="quantity" value="{{ $item->quantity }}" min="0"
                                        class="w-14 text-center text-sm py-1 border-x border-gray-200 outline-none">
                                    <button type="button" class="px-3 py-1 text-sm text-gray-600 increment">+</button>
                                </div>
                                <button type="submit"
                                    class="px-3 py-1.5 text-xs font-semibold {{ $theme->color ? 'text-' . $theme->color : 'text-blue-600' }} border {{ $theme->color ? 'border-' . $theme->color : 'border-blue-200' }} rounded-full hover:{{ $theme->color ? 'bg-' . $theme->color . '/10' : 'bg-blue-50' }} transition">
                                    Güncelle
                                </button>
                            </form>
                            <button type="button" data-cart-remove="{{ route('cart.destroy', $item->id) }}"
                                class="px-3 py-1.5 text-xs font-semibold {{ $theme->color ? 'text-' . $theme->color : 'text-red-600' }} border {{ $theme->color ? 'border-' . $theme->color : 'border-red-200' }} rounded-full hover:{{ $theme->color ? 'bg-' . $theme->color . '/10' : 'bg-red-50' }} transition">
                                Kaldır
                            </button>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Toplam</p>
                            <p
                                class="text-lg font-semibold {{ $theme->color ? 'text-' . $theme->color : 'text-gray-900' }}">
                                {{ number_format((float) $displayLineTotal, 2, ',', '.') }}
                                {{ $currencySymbol }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div
                        class="bg-white border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} rounded-2xl p-10 text-center {{ $theme->color ? 'text-' . $theme->color : 'text-gray-500' }}">
                        Sepetinizde ürün bulunmuyor.
                    </div>
                @endforelse
            </div>
            @if ($canCheckout)
                <div class="space-y-6">
                    <div
                        class="bg-white border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} rounded-2xl p-6 shadow-sm">
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
                                class="flex items-center justify-between font-semibold text-gray-900 pt-3 border-t border-gray-100">
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
                        <div class="mt-6 space-y-3">
                            @if ($canCheckout)
                                @if ((optional($store)->auth_required ?? false) && !auth()->check())
                                    <a href="{{ route('login') }}"
                                        class="block text-center flex items-center justify-center gap-4 w-full px-4 py-3 rounded-full {{ $theme->color ? 'bg-' . $theme->color : 'bg-blue-600' }} text-white text-sm font-semibold hover:bg-gray-700 transition-colors">
                                        <span><i class="ri-arrow-right-line font-light text-xl"></i></span>
                                        Giriş Yaparak Satın Al
                                    </a>
                                @else
                                    <a href="{{ route('cart.checkout') }}"
                                        class="block text-center flex items-center justify-center gap-4 w-full px-4 py-3 rounded-full {{ $theme->color ? 'bg-' . $theme->color : 'bg-blue-600' }} text-white text-sm font-semibold hover:bg-gray-700 transition-colors">
                                        <span><i class="ri-arrow-right-line font-light text-xl"></i></span>
                                        Satın Alma Aşamasına Geç
                                    </a>
                                @endif
                                <form action="{{ route('cart.clear') }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                        class="w-full text-center flex items-center justify-center gap-4 px-4 py-3 rounded-full border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} text-sm font-semibold {{ $theme->color ? 'text-' . $theme->color : 'text-gray-600' }} hover:bg-gray-100 transition-colors">
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
                        class="bg-white border {{ $theme->color ? 'border-' . $theme->color : 'border-blue-100' }} {{ $theme->color ? 'text-' . $theme->color : 'text-blue-800' }} rounded-2xl p-6 text-sm">
                        <h3 class="text-sm font-semibold mb-2">Güvenilir Alışveriş</h3>
                        <p>Mağazamızda yapacağınız alışverişlerde 256-bit SSL sertifikası ve güvenli ödeme altyapısı
                            kullanılmaktadır.</p>
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
