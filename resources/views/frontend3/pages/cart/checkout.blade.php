@extends($template . '.layouts.app')
@php
    $cart = $data->cart;
    $items = $cart?->items ?? collect();
    $addresses = $data->addresses ?? collect();
    $canCheckout = auth()->check() ? $addresses->isNotEmpty() : true;
    $selectedPaymentMethod = old('payment_method', 'card');
    $storeModel = $store ?? null;
    $wireBanks = collect($banks ?? []);
    $wireEnabled = (bool) ($storeModel?->allow_wire_payments ?? false) && $wireBanks->isNotEmpty();

    $pricingData = is_array($pricing ?? null) ? $pricing : [];
    $pricingTotals = array_merge(
        ['net' => (float) ($cart?->total_price ?? 0), 'tax' => 0.0, 'gross' => (float) ($cart?->total_price ?? 0)],
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

    $formatMoney = function ($value) {
        return number_format((float) $value, 2, ',', '.');
    };
@endphp

@section('title', 'Ödeme')
@section('breadcrumb_title', 'Siparişi Tamamla')
@section('content')
    @include($template . '.parts.breadcrumb')
    <section class="py-16">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
                @if (!$store->auth_required)
                    @auth
                    @else
                        <div class="lg:col-span-8">
                            <div
                                class="bg-indigo-50 border border-indigo-200 w-[max-content] text-indigo-600 px-3 py-2 rounded-3xl md:text-md text-xs font-semibold flex items-center gap-1">
                                <i class="ri-information-line text-indigo-600"></i>
                                Üye olarak işlemlerinizi daha hızlı hale getirebilir, sipariş ve adreslerinizi kolayca
                                yönetebilirsiniz.
                                <a href="{{ route('register') }}"
                                    class="transition-all hover:bg-indigo-600 hover:text-white bg-white px-2 py-2 rounded-2xl border border-indigo-200 text-indigo-600 hover:text-indigo-700 transition-colors inline-block ml-1">
                                    <i class="ri-arrow-right-line"></i>
                                </a>
                            </div>
                        </div>
                    @endif
                    @endif
                    <div class="lg:col-span-8">
                        <form id="checkout-form" action="{{ route('cart.checkout.start') }}" method="POST" class="space-y-12">
                            @csrf
                            @if ($errors->any())
                                <div class="bg-red-50 border border-red-200 text-red-600 px-6 py-4 rounded-3xl mb-6">
                                    <div class="flex items-start gap-3">
                                        <i class="ri-error-warning-line text-xl flex-shrink-0 mt-0.5"></i>
                                        <div class="flex-1">
                                            <h3 class="text-xs font-bold uppercase tracking-widest mb-2">Lütfen aşağıdaki
                                                hataları düzeltin:</h3>
                                            <ul class="space-y-1 text-xs">
                                                @foreach ($errors->all() as $error)
                                                    <li class="flex items-start gap-2">
                                                        <span class="text-red-500 mt-0.5">•</span>
                                                        <span>{{ $error }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div class="space-y-8">
                                <div
                                    class="flex items-end justify-between border-b {{ $theme->color ? 'border-' . $theme->color : 'border-black' }} pb-4">
                                    <h2
                                        class="text-3xl font-black uppercase tracking-tighter {{ $theme->color ? 'text-' . $theme->color : 'text-gray-900' }}">
                                        Teslimat Adresi</h2>
                                    @auth
                                        <a href="{{ route('user.addresses') }}"
                                            class="text-[10px] font-black uppercase tracking-widest text-gray-400 hover:{{ $theme->color ? 'text-' . $theme->color : 'text-black' }} transition-colors">Adreslerimi
                                            Yönet →</a>
                                    @endauth
                                </div>

                                @auth
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        @foreach ($addresses as $address)
                                            <label
                                                class="border {{ $theme->color ? 'border-' . $theme->color . '/30' : 'border-black' }} p-6 cursor-pointer hover:bg-gray-50 transition-colors relative group rounded-3xl">
                                                <input type="radio" name="address_id" value="{{ $address->id }}"
                                                    class="sr-only peer" @checked(old('address_id', $loop->first ? $address->id : null) == $address->id)>
                                                <div
                                                    class="peer-checked:{{ $theme->color ? 'border-' . $theme->color : 'border-black' }} absolute inset-0 border-2 border-transparent pointer-events-none rounded-3xl">
                                                </div>
                                                <div class="space-y-2">
                                                    <div class="flex justify-between items-start">
                                                        <span
                                                            class="text-xs font-black uppercase tracking-widest text-gray-400">{{ $address->title }}</span>
                                                        {{-- <div
                                                            class="w-4 h-4 border {{ $theme->color ? 'border-' . $theme->color . '/30' : 'border-black' }} flex items-center justify-center">
                                                            <div
                                                                class="w-2 h-2 {{ $theme->color ? 'bg-' . $theme->color : 'bg-black' }} scale-0 peer-checked:scale-100 transition-transform">
                                                            </div>
                                                        </div> --}}
                                                    </div>
                                                    <p
                                                        class="text-sm font-bold {{ $theme->color ? 'text-' . $theme->color : 'text-black' }}">
                                                        {{ $address->fullname }}</p>
                                                    <p class="text-xs text-gray-500 leading-relaxed">
                                                        {{ $address->address }}<br>{{ $address->state }} / {{ $address->city }}
                                                    </p>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                @else
                                    {{-- Misafir Formu --}}
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="space-y-2">
                                            <label class="text-[10px] font-black uppercase tracking-widest text-gray-400">Ad
                                                Soyad</label>
                                            <input type="text" name="fullname" value="{{ old('fullname') }}"
                                                class="w-full bg-white border {{ $theme->color ? 'border-' . $theme->color . '/30' : 'border-black' }} px-6 py-4 text-xs font-bold outline-none focus:border-{{ $theme->color ? $theme->color : 'blue-600' }} rounded-full">
                                        </div>
                                        <div class="space-y-2">
                                            <label
                                                class="text-[10px] font-black uppercase tracking-widest text-gray-400">E-Posta</label>
                                            <input type="email" name="email" value="{{ old('email') }}"
                                                class="w-full bg-white border {{ $theme->color ? 'border-' . $theme->color . '/30' : 'border-black' }} px-6 py-4 text-xs font-bold outline-none focus:border-{{ $theme->color ? $theme->color : 'blue-600' }} rounded-full">
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="space-y-2">
                                                <label
                                                    class="text-[10px] font-black uppercase tracking-widest text-gray-400">Telefon</label>
                                                <input type="text" name="phone" value="{{ old('phone') }}"
                                                    class="w-full bg-white border {{ $theme->color ? 'border-' . $theme->color . '/30' : 'border-black' }} px-6 py-4 text-xs font-bold outline-none focus:border-{{ $theme->color ? $theme->color : 'blue-600' }} rounded-full">
                                            </div>
                                            <div class="space-y-2">
                                                <label class="text-[10px] font-black uppercase tracking-widest text-gray-400">TC
                                                    Kimlik No @if ($storeModel && $storeModel->tc_required)
                                                        <span class="text-red-500">*</span>
                                                    @endif
                                                </label>
                                                <input type="text" name="tc" value="{{ old('tc') }}" maxlength="11"
                                                    pattern="[0-9]{11}" @if ($storeModel && $storeModel->tc_required) required @endif
                                                    class="w-full bg-white border {{ $theme->color ? 'border-' . $theme->color . '/30' : 'border-black' }} px-6 py-4 text-xs font-bold outline-none focus:border-{{ $theme->color ? $theme->color : 'blue-600' }} rounded-full">
                                            </div>
                                        </div>
                                        <div class="space-y-2">
                                            <label class="text-[10px] font-black uppercase tracking-widest text-gray-400">Şehir /
                                                İlçe</label>
                                            <div class="flex gap-2">
                                                <input type="text" name="city" placeholder="ŞEHİR"
                                                    value="{{ old('city') }}"
                                                    class="w-1/2 bg-white border {{ $theme->color ? 'border-' . $theme->color . '/30' : 'border-black' }} px-6 py-4 text-xs font-bold outline-none focus:border-{{ $theme->color ? $theme->color : 'blue-600' }} rounded-full">
                                                <input type="text" name="state" placeholder="İLÇE" value="{{ old('state') }}"
                                                    class="w-1/2 bg-white border {{ $theme->color ? 'border-' . $theme->color . '/30' : 'border-black' }} px-6 py-4 text-xs font-bold outline-none focus:border-{{ $theme->color ? $theme->color : 'blue-600' }} rounded-full">
                                            </div>
                                        </div>
                                        <div class="md:col-span-2 space-y-2">
                                            <label class="text-[10px] font-black uppercase tracking-widest text-gray-400">Açık
                                                Adres</label>
                                            <textarea name="address" rows="3"
                                                class="w-full bg-white border {{ $theme->color ? 'border-' . $theme->color . '/30' : 'border-black' }} px-6 py-4 text-xs font-bold outline-none focus:border-{{ $theme->color ? $theme->color : 'blue-600' }} rounded-3xl">{{ old('address') }}</textarea>
                                        </div>
                                    </div>
                                @endauth
                            </div>

                            {{-- Ödeme Yöntemi --}}
                            <div class="space-y-8">
                                <div class="border-b {{ $theme->color ? 'border-' . $theme->color : 'border-black' }} pb-4">
                                    <h2
                                        class="text-3xl font-black uppercase tracking-tighter {{ $theme->color ? 'text-' . $theme->color : 'text-gray-900' }}">
                                        Ödeme Yöntemi</h2>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <label
                                        class="border {{ $theme->color ? 'border-' . $theme->color . '/30' : 'border-black' }} p-8 cursor-pointer hover:bg-gray-50 transition-colors relative group rounded-full">
                                        <input type="radio" name="payment_method" value="card" class="sr-only peer"
                                            @checked(old('payment_method', 'card') === 'card')>
                                        <div
                                            class="absolute inset-0 border-2 border-transparent peer-checked:{{ $theme->color ? 'border-' . $theme->color : 'border-black' }} pointer-events-none transition-all rounded-full">
                                        </div>
                                        <div class="flex items-center gap-6">
                                            <i
                                                class="ri-bank-card-2-line text-4xl {{ $theme->color ? 'text-' . $theme->color : 'text-gray-900' }}"></i>
                                            <div>
                                                <p
                                                    class="text-sm font-black uppercase tracking-widest {{ $theme->color ? 'text-' . $theme->color : 'text-gray-900' }}">
                                                    Kredi Kartı</p>
                                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter mt-1">
                                                    GÜVENLİ ZİRAAT BANKASI ALTYAPISI</p>
                                            </div>
                                        </div>
                                    </label>

                                    @if ($wireEnabled)
                                        <label
                                            class="border {{ $theme->color ? 'border-' . $theme->color . '/30' : 'border-black' }} p-8 cursor-pointer hover:bg-gray-50 transition-colors relative group rounded-full">
                                            <input type="radio" name="payment_method" value="wire" class="sr-only peer"
                                                @checked(old('payment_method') === 'wire')>
                                            <div
                                                class="absolute inset-0 border-2 border-transparent peer-checked:{{ $theme->color ? 'border-' . $theme->color : 'border-black' }} pointer-events-none transition-all rounded-full">
                                            </div>
                                            <div class="flex items-center gap-6">
                                                <i
                                                    class="ri-exchange-dollar-line text-4xl {{ $theme->color ? 'text-' . $theme->color : 'text-gray-900' }}"></i>
                                                <div>
                                                    <p
                                                        class="text-sm font-black uppercase tracking-widest {{ $theme->color ? 'text-' . $theme->color : 'text-gray-900' }}">
                                                        Havale / EFT</p>
                                                    <p
                                                        class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter mt-1">
                                                        ÖDEME ONAYI BEKLENİR</p>
                                                </div>
                                            </div>
                                        </label>
                                    @endif
                                </div>
                                <script>
                                    document.querySelectorAll('input[name="payment_method"]').forEach((input) => {
                                        input.addEventListener('change', (e) => {
                                            const ziraatContainer = document.getElementById('ziraat-iframe-container');
                                            if (e.target.value === 'card') {
                                                ziraatContainer.classList.remove('hidden');
                                            } else {
                                                ziraatContainer.classList.add('hidden');
                                            }
                                        });
                                    });
                                </script>
                            </div>

                            <button type="submit"
                                class="w-full {{ $theme->color ? 'bg-' . $theme->color : 'bg-black' }} text-white py-6 text-sm font-black uppercase tracking-[0.3em] hover:opacity-90 transition shadow-2xl rounded-full">
                                SİPARİŞİ ONAYLA VE ÖDE
                            </button>
                        </form>
                    </div>

                    {{-- Sağ: Özet --}}
                    <div class="lg:col-span-4">
                        <div
                            class="border {{ $theme->color ? 'border-' . $theme->color . '/30' : 'border-black' }} p-8 bg-white sticky top-32 space-y-8 rounded-3xl">
                            <h2
                                class="text-2xl font-black uppercase tracking-tighter {{ $theme->color ? 'text-' . $theme->color : 'text-gray-900' }}">
                                Sipariş Özeti</h2>

                            <div class="space-y-4 max-h-60 overflow-y-auto pr-4 no-scrollbar">
                                @foreach ($items as $item)
                                    <div class="flex justify-between items-start gap-4">
                                        <span class="text-[10px] font-bold uppercase leading-tight flex-1 text-gray-500">
                                            {{ $item->product->title }}
                                            @if ($item->variant)
                                                <br><span class="text-gray-300">({{ $item->variant->term->name }})</span>
                                            @endif
                                        </span>
                                        <span class="text-[10px] font-bold text-gray-400 mr-2">{{ $item->quantity }}x</span>
                                        <span
                                            class="text-xs font-black whitespace-nowrap {{ $theme->color ? 'text-' . $theme->color : 'text-gray-900' }}">{{ $formatMoney($item->subtotal) }}
                                            ₺</span>
                                    </div>
                                @endforeach
                            </div>

                            <dl
                                class="space-y-4 text-xs font-black uppercase tracking-widest pt-8 border-t {{ $theme->color ? 'border-' . $theme->color . '/20' : 'border-black' }}">

                                @if ($cart && $cart->discount_amount > 0)
                                    <div class="flex justify-between text-green-600">
                                        <dt>İndirim ({{ $cart->appliedPromotion->code }})</dt>
                                        <dd>- {{ $formatMoney($cart->discount_amount) }} ₺</dd>
                                    </div>
                                @endif

                                @if ($storeModel && $storeModel->tax_enabled && $storeModel->tax_rate > 0)
                                    @php
                                        $productSubtotal = (float) $cart->total_price;
                                        $taxAmountDisplay = $productSubtotal * ($storeModel->tax_rate / 100);
                                    @endphp
                                    <div class="flex justify-between text-gray-400">
                                        <dt>KDV (%{{ (int) $storeModel->tax_rate }})</dt>
                                        <dd>{{ $formatMoney($taxAmountDisplay) }} ₺</dd>
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
                                    <div class="flex justify-between text-gray-900">
                                        <dt>Kargo Ücreti</dt>
                                        <dd>{{ $formatMoney($storeModel->shipping_price) }} ₺</dd>
                                    </div>
                                @endif

                                <div
                                    class="flex justify-between text-lg tracking-tighter {{ $theme->color ? 'text-' . $theme->color : 'text-gray-900' }}">
                                    <dt>Toplam</dt>
                                    <dd>{{ $formatMoney($pricingTotals['gross'] ?? 0) }} ₺</dd>
                                </div>
                            </dl>

                            <div class="p-6 bg-gray-50 border border-gray-100 flex items-center gap-4 rounded-3xl">
                                <i class="ri-shield-check-line text-3xl text-gray-300"></i>
                                <p class="text-[9px] font-bold uppercase tracking-widest text-gray-400 leading-relaxed">
                                    ÖDEMELERİNİZ 256-BIT SSL İLE ŞİFRELENMEKTEDİR.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endsection
