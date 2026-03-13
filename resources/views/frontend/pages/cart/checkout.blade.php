@extends('frontend.layouts.app')
@php
    $cart = $data->cart;
    $items = $cart?->items ?? collect();
    $addresses = $data->addresses ?? collect();
    $canCheckout = auth()->check() ? $addresses->isNotEmpty() : true;
    $selectedPaymentMethod = old('payment_method', 'card');
    $storeModel = $store ?? null;
    $wireBanks = collect($banks ?? []);
    $wireEnabled = (bool) ($storeModel?->allow_wire_payments ?? false) && $wireBanks->isNotEmpty();
    if (!$wireEnabled && $selectedPaymentMethod === 'wire') {
        $selectedPaymentMethod = 'card';
    }
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
    if ($cart && $storeModel && $storeModel->tax_enabled && $storeModel->tax_rate > 0) {
        $taxAmount = $subtotal * ($storeModel->tax_rate / 100);
    }
    $baseWithTax = $subtotal + $taxAmount;
    $shippingCost = 0;
    if (
        $cart &&
        $storeModel &&
        $storeModel->shipping_price_limit > 0 &&
        $baseWithTax < $storeModel->shipping_price_limit
    ) {
        $shippingCost = (float) ($storeModel->shipping_price ?? 0);
    }
    $pricingTotals['gross'] = $baseWithTax + $shippingCost;
    if ($cart && $cart->discount_amount > 0) {
        $pricingTotals['gross'] = (float) $cart->total_price;
        // Kargo ücretini tekrar ekle (eğer varsa)
        if ($storeModel && $cart->total_price <= $storeModel->shipping_price_limit) {
            $pricingTotals['gross'] += (float) ($storeModel->shipping_price ?? 0);
        }
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
    $formatMoney = function ($value) {
        return number_format((float) $value, 2, ',', '.');
    };
@endphp
@section('title', 'Siparişi Tamamla')
@section('breadcrumb_title', 'Siparişi Tamamla')
@section('content')
    @include('frontend.parts.breadcrumb')
    <section class="py-10">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-3 gap-10">
            <form action="{{ route('cart.checkout.start') }}" method="POST" class="lg:col-span-2 space-y-8 mt-0">
                @csrf
                <div
                    class="bg-white border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} rounded-3xl shadow-sm p-6 space-y-6 chekcout-form">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2
                                class="text-lg font-semibold {{ $theme->color ? 'text-' . $theme->color : 'text-gray-900' }}">
                                Teslimat Bilgileri</h2>
                            <p class="text-sm text-gray-600 mt-1">
                                {{ auth()->check() ? 'Kayıtlı adreslerinizden seçim yapın.' : 'Üyelik olmadan teslimat ve fatura bilgilerinizi girin.' }}
                            </p>
                        </div>
                        @auth
                            <a href="{{ route('user.addresses') }}"
                                class="inline-flex items-center gap-2 text-sm font-semibold {{ $theme->color ? 'text-' . $theme->color : 'text-blue-600' }} hover:{{ $theme->color ? 'text-' . $theme->color . '/70' : 'text-blue-700' }}">
                                Adreslerimi Yönet
                                <i class="ri-arrow-right-line text-base"></i>
                            </a>
                        @endauth
                    </div>
                    @auth
                        @php
                            $selectedAddress = old('address_id', optional($addresses->first())->id);
                        @endphp
                        <div class="space-y-4">
                            @forelse ($addresses as $address)
                                <label
                                    class="flex items-start gap-4 p-4 border rounded-2xl cursor-pointer transition hover:border-blue-500 {{ $loop->first ? 'border-blue-500' : 'border-gray-200' }}">
                                    <input type="radio" name="address_id" value="{{ $address->id }}" class="mt-1"
                                        @checked($selectedAddress === $address->id)>
                                    <div>
                                        <p class="font-semibold text-gray-900">
                                            {{ $address->title ?? 'Adres' }}
                                            @if ($address->is_default)
                                                <span
                                                    class="ml-2 text-xs px-2 py-0.5 bg-green-100 text-green-700 rounded-full">Varsayılan</span>
                                            @endif
                                        </p>
                                        <p class="text-sm text-gray-600">{{ $address->fullname }} •
                                            {{ $address->phone }}</p>
                                        <p class="text-sm text-gray-500 mt-1">
                                            {{ $address->address }} <br>
                                            {{ $address->state }} / {{ $address->city }} {{ $address->zip }}
                                        </p>
                                        <p class="text-xs text-gray-400 mt-1">{{ $address->email }}</p>
                                    </div>
                                </label>
                            @empty
                                <div class="text-sm text-gray-500">
                                    Henüz adres eklemediniz. Yeni adres oluşturmak için yukarıdaki bağlantıyı
                                    kullanabilirsiniz.
                                </div>
                            @endforelse
                        </div>
                    @else
                        @php
                            $guestAddress = $data->guestAddress ?? [];
                        @endphp
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label
                                    class="block text-sm font-medium {{ $theme->color ? 'text-' . $theme->color : 'text-gray-700' }}">Adres
                                    Başlığı</label>
                                <input type="text" name="title" value="{{ old('title', $guestAddress['title'] ?? '') }}"
                                    class="mt-2 w-full px-4 py-3 border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} rounded-xl focus:border-blue-500 focus:ring-0">
                                @error('title')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium {{ $theme->color ? 'text-' . $theme->color : 'text-gray-700' }}">Ad
                                    Soyad</label>
                                <input type="text" name="fullname"
                                    value="{{ old('fullname', $guestAddress['fullname'] ?? '') }}"
                                    class="mt-2 w-full px-4 py-3 border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} rounded-xl focus:border-blue-500 focus:ring-0">
                                @error('fullname')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium {{ $theme->color ? 'text-' . $theme->color : 'text-gray-700' }}">E-posta</label>
                                <input type="email" name="email" value="{{ old('email', $guestAddress['email'] ?? '') }}"
                                    class="mt-2 w-full px-4 py-3 border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} rounded-xl focus:border-blue-500 focus:ring-0">
                                @error('email')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium {{ $theme->color ? 'text-' . $theme->color : 'text-gray-700' }}">Telefon</label>
                                <input type="text" name="phone" value="{{ old('phone', $guestAddress['phone'] ?? '') }}"
                                    class="mt-2 w-full px-4 py-3 border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} rounded-xl focus:border-blue-500 focus:ring-0">
                                @error('phone')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium {{ $theme->color ? 'text-' . $theme->color : 'text-gray-700' }}">Şehir</label>
                                <input type="text" name="city" value="{{ old('city', $guestAddress['city'] ?? '') }}"
                                    class="mt-2 w-full px-4 py-3 border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} rounded-xl focus:border-blue-500 focus:ring-0">
                                @error('city')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium {{ $theme->color ? 'text-' . $theme->color : 'text-gray-700' }}">İlçe</label>
                                <input type="text" name="state" value="{{ old('state', $guestAddress['state'] ?? '') }}"
                                    class="mt-2 w-full px-4 py-3 border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} rounded-xl focus:border-blue-500 focus:ring-0">
                                @error('state')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium {{ $theme->color ? 'text-' . $theme->color : 'text-gray-700' }}">Posta
                                    Kodu</label>
                                <input type="text" name="zip" value="{{ old('zip', $guestAddress['zip'] ?? '') }}"
                                    class="mt-2 w-full px-4 py-3 border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} rounded-xl focus:border-blue-500 focus:ring-0">
                                @error('zip')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium {{ $theme->color ? 'text-' . $theme->color : 'text-gray-700' }}">Adres</label>
                            <textarea name="address" rows="3"
                                class="mt-2 w-full px-4 py-3 border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} rounded-xl focus:border-blue-500 focus:ring-0">{{ old('address', $guestAddress['address'] ?? '') }}</textarea>
                            @error('address')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @endauth
                    <div>
                        <label
                            class="block text-sm font-medium {{ $theme->color ? 'text-' . $theme->color : 'text-gray-700' }}">Sipariş
                            Notu (Opsiyonel)</label>
                        <textarea name="notes" rows="3"
                            class="mt-2 w-full px-4 py-3 border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} rounded-xl focus:border-blue-500 focus:ring-0"
                            placeholder="Teslimat için özel notlarınız varsa paylaşabilirsiniz.">{{ old('notes') }}</textarea>
                    </div>
                    <div class="space-y-3">
                        <p class="text-sm font-semibold {{ $theme->color ? 'text-' . $theme->color : 'text-gray-900' }}">
                            Ödeme Yöntemi</p>
                        @error('payment_method')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <label
                                class="checkout-payment-option {{ $selectedPaymentMethod === 'card' ? 'is-selected' : '' }}"
                                data-payment-option>
                                <input type="radio" name="payment_method" value="card" class="sr-only"
                                    @checked($selectedPaymentMethod === 'card')>
                                <div class="option-icon">
                                    <i class="ri-bank-card-line"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">Kredi / Banka Kartı</p>
                                    <p class="text-xs text-gray-500">Iyzico güvenli ödeme altyapısı ile anında ödeme.</p>
                                </div>
                                <span class="option-check"><i class="ri-check-line"></i></span>
                            </label>
                            @if ($wireEnabled)
                                <label
                                    class="checkout-payment-option {{ $selectedPaymentMethod === 'wire' ? 'is-selected' : '' }}"
                                    data-payment-option>
                                    <input type="radio" name="payment_method" value="wire" class="sr-only"
                                        @checked($selectedPaymentMethod === 'wire')>
                                    <div class="option-icon">
                                        <i class="ri-exchange-dollar-line"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900">Havale / EFT</p>
                                        <p class="text-xs text-gray-500">Havale/EFT ile ödeme talimatını oluşturun.</p>
                                    </div>
                                    <span class="option-check"><i class="ri-check-line"></i></span>
                                </label>
                            @endif
                        </div>
                        @if ($wireEnabled)
                            <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-4 space-y-2">
                                <p class="text-xs font-semibold text-emerald-900 tracking-wide uppercase">Aktif Bankalar
                                </p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($wireBanks as $bank)
                                        <span
                                            class="px-3 py-1 rounded-full bg-white text-xs {{ $theme->color ? 'text-' . $theme->color : 'text-gray-700' }} border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} font-semibold">
                                            {{ $bank->bank_name }} • {{ $bank->bank_iban }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                    <script>
                        document.querySelectorAll('input[name="payment_method"]').forEach((input) => {
                            input.addEventListener('change', (e) => {
                                const ziraatContainer = document.getElementById('ziraat-iframe-container');
                                if (ziraatContainer) {
                                    if (e.target.value === 'card') {
                                        ziraatContainer.classList.remove('hidden');
                                    } else {
                                        ziraatContainer.classList.add('hidden');
                                    }
                                }
                            });
                        });
                    </script>
                    <div id="ziraat-iframe-container" class="mt-6 hidden">
                        @if (isset($paymentData) && isset($paymentData['action']))
                            @include('frontend.parts.ziraat_iframe')
                        @endif
                    </div>
                    @if ($errors->any())
                        <div class="bg-red-50 border border-red-100 text-red-700 rounded-2xl p-4 text-sm">
                            <p>Formda bazı eksikler var, lütfen kontrol edin.</p>
                        </div>
                    @endif
                    <button type="submit"
                        class="w-full px-6 py-3 rounded-full flex items-center justify-center gap-4 hover:border hover:border-{{ $theme->color ? '' . $theme->color : 'border-blue-600' }} {{ $theme->color ? 'bg-' . $theme->color : 'bg-blue-600' }} text-sm font-semibold hover:bg-white text-white hover:text-{{ $theme->color ? $theme->color : 'text-blue-600' }} transition-colors disabled:opacity-50"
                        {{ $items->isEmpty() || !$canCheckout ? 'disabled' : '' }}>
                        <span><i class="ri-money-dollar-circle-line font-light text-xl"></i></span>
                        Ödemeyi Tamamla
                    </button>
                </div>
            </form>
            <div class="space-y-6">
                <div
                    class="bg-white border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} rounded-3xl p-6 shadow-sm">
                    <h2
                        class="text-lg font-semibold {{ $theme->color ? 'text-' . $theme->color : 'text-gray-900' }} mb-4">
                        Sipariş Özeti</h2>
                    <div class="space-y-3 max-h-80 overflow-y-auto pr-2">
                        @forelse ($items as $item)
                            <div class="flex items-center justify-between">
                                <div>
                                    <p
                                        class="font-light text-xs {{ $theme->color ? 'text-' . $theme->color : 'text-gray-900' }}">
                                        {{ $item->product?->title ?? 'Ürün' }}</p>
                                    <p class="text-xs {{ $theme->color ? 'text-' . $theme->color : 'text-gray-500' }}">
                                        Adet: {{ $item->quantity }}</p>
                                </div>
                                <span
                                    class="font-semibold {{ $theme->color ? 'text-' . $theme->color : 'text-gray-900' }}">
                                    {{ number_format((float) $item->subtotal, 2, ',', '.') }} ₺
                                </span>
                            </div>
                        @empty
                            <p class="text-sm {{ $theme->color ? 'text-' . $theme->color : 'text-gray-500' }}">Sepetiniz
                                boş.</p>
                        @endforelse
                    </div>
                    <div class="mt-4 space-y-2 text-sm text-gray-500">
                        <div class="flex items-center justify-between">
                            <span>Ara Toplam (Vergi Hariç)</span>
                            <span>{{ $formatMoney($pricingTotals['net'] ?? 0) }} ₺</span>
                        </div>
                        @if ($taxEnabled)
                            @if (!empty($taxBreakdown))
                                @foreach ($taxBreakdown as $taxLine)
                                    <div class="flex items-center justify-between">
                                        <span>Vergi ({{ $taxLine['label'] ?? '' }})</span>
                                        <span>{{ $formatMoney($taxLine['amount'] ?? 0) }} ₺</span>
                                    </div>
                                @endforeach
                            @else
                                <div class="flex items-center justify-between">
                                    <span>Vergi</span>
                                    <span>{{ $formatMoney($pricingTotals['tax'] ?? 0) }} ₺</span>
                                </div>
                            @endif
                        @else
                            <div class="flex items-center justify-between">
                                <span>Vergi</span>
                                <span>—</span>
                            </div>
                        @endif
                        @php
                            $currentBaseWithTax =
                                $cart->total_price +
                                ($storeModel && $storeModel->tax_enabled
                                    ? $cart->total_price * ($storeModel->tax_rate / 100)
                                    : 0);
                        @endphp
                        @if ($storeModel && $currentBaseWithTax < $storeModel->shipping_price_limit && $storeModel->shipping_price > 0)
                            <div class="flex items-center justify-between">
                                <span>Kargo Ücreti</span>
                                <span>{{ $formatMoney($storeModel->shipping_price) }} ₺</span>
                            </div>
                        @endif
                    </div>
                    <div
                        class="border-t {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} mt-4 pt-4 flex items-center justify-between font-semibold {{ $theme->color ? 'text-' . $theme->color : 'text-gray-900' }}">
                        <span>Genel Toplam (Vergi Dahil)</span>
                        <span>{{ $formatMoney($pricingTotals['gross'] ?? 0) }} ₺</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        @if ($taxEnabled)
                            Vergiler ürün ayarlarına göre sepete dahil edilmiştir.
                        @else
                            Mağaza vergi uygulaması kapalıdır; tutarlar vergi içermeyebilir.
                        @endif
                    </p>
                </div>
                <div
                    class="{{ $theme->color ? 'bg-' . $theme->color . '/10' : 'bg-gray-900' }} {{ $theme->color ? 'text-' . $theme->color : 'text-white' }} rounded-3xl p-6 space-y-4">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">🔒</span>
                        <div>
                            <p class="font-semibold">Güvenli Ödeme</p>
                            <p class="text-xs {{ $theme->color ? 'text-' . $theme->color : 'text-gray-300' }}">İyzico
                                altyapısı ile korunur.</p>
                        </div>
                    </div>
                    <p class="text-sm {{ $theme->color ? 'text-' . $theme->color : 'text-gray-200' }}">Ödeme ekranında
                        kart bilgileri doğrudan İyzico tarafında işlenir.
                        Mağazamızda saklanmaz.</p>
                </div>
            </div>
        </div>
    </section>
@endsection
