@extends($template . '.layouts.app')
@php
    $cart = $data->cart;
    $items = $cart?->items ?? collect();
    $canCheckout = $items->count() > 0;
    $pricingData = is_array($pricing ?? null) ? $pricing : [];
    $pricingTotals = $pricingData['totals'] ?? [];

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
    $formatMoney = function ($value) {
        return number_format((float) $value, 2, ',', '.');
    };
@endphp

@section('title', 'Alışveriş Sepeti')
@section('breadcrumb_title', 'Sepetiniz')

@section('content')
    @include($template . '.parts.breadcrumb')

    <section class="py-16">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Hata Mesajları --}}
            @if ($errors->any())
                <div class="mb-8 bg-red-50 border border-red-200 text-red-600 px-6 py-4 rounded-[2rem]">
                    <ul class="space-y-1 text-sm font-semibold">
                        @foreach ($errors->all() as $error)
                            <li class="flex items-center gap-2">
                                <i class="ri-error-warning-line text-lg"></i>
                                {{ $error }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
                {{-- Ürün Listesi --}}
                <div class="lg:col-span-8 space-y-4">
                    @forelse ($items as $item)
                        <div
                            class="bg-white rounded-[2rem] border {{ $theme->color ? 'border-' . $theme->color . '/25' : 'border-gray-200' }} p-4 flex flex-col md:flex-row gap-4 md:items-center">
                            <div
                                class="w-28 rounded-2xl aspect-square {{ $theme->color ? 'bg-' . $theme->color . '/5' : 'bg-gray-50' }} border {{ $theme->color ? 'border-' . $theme->color . '/10' : 'border-gray-100' }} flex-shrink-0">
                                @if ($item->product?->photo)
                                    <img src="{{ asset($item->product->photo) }}"
                                        class="w-full h-full object-cover rounded-2xl">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-200">
                                        <i class="ri-image-line text-3xl"></i>
                                    </div>
                                @endif
                            </div>

                            <div class="flex-1 space-y-2">
                                <h3 class="text-lg font-semibold uppercase tracking-tighter text-gray-900">
                                    {{ $item->product?->title ?? 'Ürün' }}
                                </h3>
                                @php
                                    $itemVariants = $item->variants();
                                @endphp
                                @if ($itemVariants->isNotEmpty())
                                    <div class="space-y-1">
                                        @foreach ($itemVariants as $v)
                                            @php
                                                $termName = $v->term?->name ?? '';
                                                $colorMatch = null;
                                                $displayName = $termName;
                                                if (preg_match('/#([a-fA-F0-9]{3}){1,2}/', $termName, $matches)) {
                                                    $colorMatch = $matches[0];
                                                    $displayName = trim(str_replace($colorMatch, '', $termName));
                                                }
                                            @endphp
                                            <p
                                                class="text-[10px] font-semibold uppercase tracking-widest text-gray-400 flex items-center gap-2">
                                                <span>{{ $v->attribute->name }}:</span>
                                                @if ($colorMatch)
                                                    <span class="w-2.5 h-2.5 rounded-full border border-gray-200"
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
                                    <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400 flex items-center gap-2">
                                        <span>{{ $item->variant->attribute->name }}:</span>
                                        @if ($colorMatch)
                                            <span class="w-2.5 h-2.5 rounded-full border border-gray-200"
                                                style="background-color: {{ $colorMatch }}"></span>
                                        @endif
                                        <span>{{ $displayName }}</span>
                                    </p>
                                @endif
                                <p
                                    class="text-sm font-bold {{ $theme->color ? 'text-' . $theme->color : 'text-gray-900' }}">
                                    {{ $formatMoney($item->price) }} ₺
                                </p>
                            </div>

                            <div class="flex items-center md:flex-row flex-col gap-4">
                                <form action="{{ route('cart.update', $item->id) }}" method="POST"
                                    class="flex items-stretch border rounded-full border {{ $theme->color ? 'border-' . $theme->color . '/30' : 'border-gray-200' }}"
                                    data-cart-update-form>
                                    @csrf @method('PATCH')
                                    <button type="button"
                                        class="w-10 flex rounded-full items-center justify-center text-xl font-bold hover:bg-gray-50 decrement text-gray-600">-</button>
                                    <input type="number" name="quantity" value="{{ $item->quantity }}" min="0"
                                        class="w-12 text-center font-semibold outline-none bg-transparent text-gray-900">
                                    <button type="button"
                                        class="w-10 flex rounded-full items-center justify-center text-xl font-bold hover:bg-gray-50 increment text-gray-600">+</button>

                                    <button type="submit"
                                        class="ml-2 rounded-full px-4 py-2 text-xs font-semibold uppercase tracking-widest {{ $theme->color ? 'text-' . $theme->color . ' border-l border-' . $theme->color . '/30 hover:bg-' . $theme->color . '/5' : 'text-blue-600 border-l border-gray-200 hover:bg-blue-50' }} transition">
                                        Güncelle
                                    </button>
                                </form>

                                <div class="text-right min-w-[100px]">
                                    <p
                                        class="text-lg font-semibold {{ $theme->color ? 'text-' . $theme->color : 'text-gray-900' }}">
                                        {{ $formatMoney($item->subtotal) }} ₺
                                    </p>
                                </div>

                                <button type="button" data-cart-remove="{{ route('cart.destroy', $item->id) }}"
                                    class="rounded-full px-4 py-2 text-xs font-semibold uppercase tracking-widest {{ $theme->color ? 'text-red-600 border border-red-200 hover:bg-red-50' : 'text-red-600 border border-red-200 hover:bg-red-50' }} transition">
                                    Kaldır
                                </button>
                            </div>
                        </div>
                    @empty
                        <div
                            class="border {{ $theme->color ? 'border-' . $theme->color . '/20' : 'border-gray-200' }} bg-white p-20 text-center rounded-[2rem]">
                            <p class="text-xl font-semibold uppercase tracking-widest text-gray-400">SEPETİNİZ ŞU ANDA BOŞ.
                            </p>
                            <a href="{{ route('products.index') }}"
                                class="inline-block mt-8 px-12 py-4 {{ $theme->color ? 'bg-' . $theme->color . ' hover:opacity-90' : 'bg-black hover:bg-gray-800' }} text-white text-xs font-semibold uppercase tracking-widest transition rounded-full">ALIŞVERİŞE
                                BAŞLA</a>
                        </div>
                    @endforelse

                    {{-- Kupon Alanı --}}
                    @if ($canCheckout)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-12">
                            <div
                                class="rounded-3xl border {{ $theme->color ? 'border-' . $theme->color . '/25' : 'border-gray-200' }} p-6 {{ $cart && $cart->applied_promotion_id ? 'bg-gray-50 opacity-50' : 'bg-white' }}">
                                <label
                                    class="text-[10px] font-semibold uppercase tracking-widest mb-4 block text-gray-400">İndirim
                                    Kuponu</label>
                                <div class="flex">
                                    <input type="text" id="coupon_code_input" placeholder="KODU GİRİN"
                                        @disabled($cart && $cart->applied_promotion_id)
                                        class="rounded-full rounded-r-none flex-1 px-6 py-3 border {{ $theme->color ? 'border-' . $theme->color . '/30' : 'border-gray-200' }} border-r-0 outline-none uppercase font-semibold tracking-widest text-xs">
                                    <button type="button" id="apply_coupon_btn" @disabled($cart && $cart->applied_promotion_id)
                                        class="rounded-full rounded-l-none px-8 {{ $theme->color ? 'bg-' . $theme->color : 'bg-black' }} text-white text-xs font-semibold uppercase tracking-widest hover:opacity-90 transition">UYGULA</button>
                                </div>
                            </div>

                            @if ($data->availablePromotions->isNotEmpty())
                                <div
                                    class="rounded-3xl border {{ $theme->color ? 'border-' . $theme->color . '/25' : 'border-gray-200' }} p-6 bg-white">
                                    <label
                                        class="text-[10px] font-semibold uppercase tracking-widest mb-4 block text-gray-400">Kullanılabilir
                                        Kuponlar</label>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($data->availablePromotions as $promo)
                                            <button type="button"
                                                class="promotion-card flex items-center gap-2 px-4 py-2 rounded-full border {{ $theme->color ? 'border-' . $theme->color . '/30 text-' . $theme->color . ' hover:bg-' . $theme->color . ' hover:text-white' : 'border-gray-200 text-gray-600 hover:bg-black hover:text-white' }} transition-all duration-300 {{ $cart && $cart->applied_promotion_id ? 'opacity-50 cursor-not-allowed' : '' }}"
                                                data-promotion-id="{{ $promo->id }}"
                                                data-promotion-code="{{ $promo->code }}"
                                                data-promotion-discount="{{ $promo->discount_rate }}"
                                                @disabled($cart && $cart->applied_promotion_id)>
                                                <i class="ri-gift-line"></i>
                                                <span class="text-xs font-bold tracking-widest">{{ $promo->code }}</span>
                                                <span class="text-[10px] opacity-75">(-%{{ $promo->discount_rate }})</span>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Özet Alanı --}}
                @if ($canCheckout)
                    <div class="lg:col-span-4">
                        <div
                            class="border rounded-3xl {{ $theme->color ? 'border-' . $theme->color . '/30' : 'border-gray-200' }} p-8 bg-white sticky top-32 space-y-8">
                            <h2
                                class="text-2xl font-semibold uppercase tracking-tighter {{ $theme->color ? 'text-' . $theme->color : 'text-gray-900' }}">
                                Sipariş Özeti</h2>

                            <div class="space-y-3 pb-6 border-b border-gray-100 mb-6">
                                @foreach ($items as $item)
                                    <div class="flex justify-between text-xs font-semibold text-gray-900">
                                        <span class="flex-1 truncate mr-2">{{ $item->product?->title }}</span>
                                        <span class="mr-4">{{ $item->quantity }}x</span>
                                        <span class="whitespace-nowrap">{{ $formatMoney($item->subtotal) }} ₺</span>
                                    </div>
                                @endforeach
                            </div>

                            <dl class="space-y-4 text-xs font-semibold uppercase tracking-widest">
                                <div class="flex justify-between">
                                    <dt class="text-gray-400">Ürün Sayısı</dt>
                                    <dd class="text-gray-900">{{ $cart?->total_items ?? 0 }}</dd>
                                </div>
                                @if ($cart && $cart->discount_amount > 0)
                                    <div class="flex justify-between text-green-600">
                                        <dt>İndirim ({{ $cart->appliedPromotion->code }})</dt>
                                        <dd>- {{ $formatMoney($cart->discount_amount) }} ₺</dd>
                                    </div>
                                @endif

                                @if ($store->tax_enabled && $store->tax_rate > 0)
                                    @php
                                        $productSubtotal = (float) $cart->total_price;
                                        $taxAmountDisplay = $productSubtotal * ($store->tax_rate / 100);
                                    @endphp
                                    <div class="flex justify-between text-gray-700 border-t border-gray-100 pt-2">
                                        <dt>KDV (%{{ (int) $store->tax_rate }})</dt>
                                        <dd>{{ $formatMoney($taxAmountDisplay) }} ₺</dd>
                                    </div>
                                @endif

                                @php
                                    $currentBaseWithTax =
                                        $cart->total_price +
                                        ($store->tax_enabled ? $cart->total_price * ($store->tax_rate / 100) : 0);
                                @endphp

                                @if ($currentBaseWithTax < $store->shipping_price_limit && $store->shipping_price > 0)
                                    <div class="flex justify-between text-gray-900">
                                        <dt>Kargo Ücreti</dt>
                                        <dd>{{ $formatMoney($store->shipping_price) }} ₺</dd>
                                    </div>
                                @endif

                                <div
                                    class="flex justify-between pt-4 border-t border-gray-100 text-lg tracking-tighter {{ $theme->color ? 'text-' . $theme->color : 'text-gray-900' }}">
                                    <dt>Genel Toplam</dt>
                                    <dd>{{ $formatMoney($pricingTotals['gross'] ?? 0) }} ₺</dd>
                                </div>
                            </dl>

                            <div class="space-y-3 pt-4">
                                @if ($store->auth_required && !auth()->check())
                                    <a href="{{ route('login') }}"
                                        class="block w-full rounded-full {{ $theme->color ? 'bg-' . $theme->color : 'bg-black' }} text-white py-5 text-center text-xs font-semibold uppercase tracking-[0.2em] hover:opacity-90 transition shadow-2xl">
                                        Giriş Yaparak Satın Al
                                    </a>
                                @else
                                    <a href="{{ route('cart.checkout') }}"
                                        class="block w-full rounded-full {{ $theme->color ? 'bg-' . $theme->color : 'bg-black' }} text-white py-5 text-center text-xs font-semibold uppercase tracking-[0.2em] hover:opacity-90 transition shadow-2xl">
                                        ÖDEME ADIMINA GEÇ
                                    </a>
                                @endif
                                <form action="{{ route('cart.clear') }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                        class="w-full border rounded-full {{ $theme->color ? 'border-' . $theme->color . '/30 text-' . $theme->color : 'border-gray-200 text-gray-600' }} py-4 text-xs font-semibold uppercase tracking-widest hover:bg-gray-50 transition">
                                        SEPETİ BOŞALT
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
