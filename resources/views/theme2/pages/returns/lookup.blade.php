@php
    use Illuminate\Support\Str;
    $order = $data->order ?? null;
    $lookup = $data->lookup ?? null;
    $returns = $order?->returns ?? collect();
    $selectedItems = collect(old('items', request('items', [])))
        ->map(fn($id) => (int) $id)
        ->all();
    $returnedItemIds = $returns
        ->flatMap(function ($return) {
            return $return->items->pluck('order_item_id');
        })
        ->unique()
        ->all();
@endphp
@extends('frontend.layouts.app')
@section('title', 'Sipariş Sorgulama & İade')
@section('breadcrumb_title', 'Sipariş Sorgulama')
@section('content')
    @include('frontend.parts.breadcrumb')
    <section class="py-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white border border-black/10 shadow-sm p-5">
                <h2 class="text-base font-semibold text-gray-900">Sipariş Numaranızı Girin</h2>
                <p class="text-xs text-gray-500 mt-1">
                    Sipariş numaranız ve e-posta adresiniz ile siparişinizi bulun, ürünleri seçerek iade talebi oluşturun.
                </p>
                <form method="GET" action="{{ route('returns.lookup') }}"
                    class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3.5">
                    <div>
                        <label
                            class="block text-xs font-semibold {{ $theme->color ? 'text-' . $theme->color : 'text-gray-700' }}">Sipariş
                            Numarası</label>
                        <input type="text" name="order_number" value="{{ old('order_number', request('order_number')) }}"
                            class="mt-1.5 w-full px-3.5 py-2 border border-black/10 text-sm focus:border-blue-500 focus:ring-0"
                            placeholder="Örn: ORD20250001">
                        @error('order_number')
                            <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label
                            class="block text-xs font-semibold {{ $theme->color ? 'text-' . $theme->color : 'text-gray-700' }}">E-posta</label>
                        <input type="email" name="email" value="{{ old('email', request('email')) }}"
                            class="mt-1.5 w-full px-3.5 py-2 border border-black/10 text-sm focus:border-blue-500 focus:ring-0"
                            placeholder="Sipariş verirken kullandığınız e-posta">
                        @error('email')
                            <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex items-end">
                        <button type="submit"
                            class="w-full px-4 py-2 flex items-center justify-center gap-2 {{ $theme->color ? 'bg-' . $theme->color : 'bg-blue-600' }} text-white text-xs font-semibold hover:{{ $theme->color ? 'bg-' . $theme->color . '/30' : 'bg-black' }} transition-colors">
                            <i class="ri-search-line text-base"></i>
                            <span>Siparişi Bul</span>
                        </button>
                    </div>
                </form>
            </div>
            @if ($order)
                <div class="bg-white border border-black/10 shadow-sm p-5 space-y-5">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                        <div>
                            <p class="text-xs text-gray-500">Sipariş No</p>
                            <p class="text-xl font-semibold text-gray-900">#{{ $order->order_number }}</p>
                        </div>
                        @if ($returns->isNotEmpty())
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1 bg-yellow-50 text-yellow-700 text-xs font-medium">
                                <i class="ri-information-line text-base"></i>
                                Bu sipariş için iade talebi bulunuyor
                            </span>
                        @endif
                        @if ($order->status === 'canceled')
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1 bg-red-50 text-red-700 text-xs font-medium">
                                <i class="ri-error-warning-line text-base"></i>
                                Bu sipariş iptal edilmiştir.
                            </span>
                        @endif
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="flex items-center gap-3 p-3 border border-black/10 bg-gray-50/50">
                            <span class="inline-flex items-center justify-center text-xl text-blue-600">
                                <i class="ri-file-list-3-line"></i>
                            </span>
                            <div>
                                <p class="text-[11px] uppercase tracking-wide text-gray-500">Sipariş Durumu</p>
                                @php
                                    $statusMeta = [
                                        'new' => ['label' => 'Yeni', 'class' => 'bg-blue-100 text-blue-700'],
                                        'pending' => [
                                            'label' => 'Beklemede',
                                            'class' => 'bg-blue-100 text-blue-700',
                                        ],
                                        'completed' => [
                                            'label' => 'Tamamlandı',
                                            'class' => 'bg-green-100 text-green-700',
                                        ],
                                        'canceled' => ['label' => 'İptal Edildi', 'class' => 'bg-red-100 text-red-700'],
                                    ];
                                    $status = $statusMeta[$order->status] ?? [
                                        'label' => ucfirst($order->status),
                                        'class' => 'bg-gray-100 text-gray-700',
                                    ];
                                @endphp
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 mt-1 text-[11px] font-semibold {{ $status['class'] }}">
                                    {{ $status['label'] }}
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-3 border border-black/10 bg-gray-50/50">
                            <span class="inline-flex items-center justify-center text-xl text-indigo-600">
                                <i class="ri-bank-card-line"></i>
                            </span>
                            <div>
                                <p class="text-[11px] uppercase tracking-wide text-gray-500">Ödeme Yöntemi</p>
                                @php
                                    $methodMeta = [
                                        'card' => [
                                            'label' => 'Kredi / Banka Kartı',
                                            'class' => 'text-indigo-600',
                                            'icon' => 'ri-bank-card-line',
                                        ],
                                        'wire' => [
                                            'label' => 'Havale / EFT',
                                            'class' => 'text-emerald-600',
                                            'icon' => 'ri-exchange-dollar-line',
                                        ],
                                    ];
                                    $method = $methodMeta[$order->method] ?? [
                                        'label' => ucfirst($order->method ?? 'Bilinmiyor'),
                                        'class' => 'text-gray-600',
                                        'icon' => 'ri-question-line',
                                    ];
                                @endphp
                                <span class="inline-flex items-center gap-1 text-xs font-semibold {{ $method['class'] }}">
                                    <i class="{{ $method['icon'] }}"></i>
                                    {{ $method['label'] }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="p-3.5 border border-black/10 bg-gray-50/50">
                            <div class="flex items-center gap-2 mb-1.5">
                                <span class="inline-flex items-center justify-center text-base text-slate-600">
                                    <i class="ri-map-pin-user-line"></i>
                                </span>
                                <div>
                                    <p class="text-[11px] uppercase tracking-wide text-gray-500">Teslimat Adresi</p>
                                    <p class="text-xs font-semibold text-gray-900">
                                        {{ $order->shippingAddress?->fullname ?? 'Belirtilmemiş' }}</p>
                                </div>
                            </div>
                            <p class="text-xs text-gray-600 leading-relaxed">
                                {{ $order->shippingAddress?->address ?? 'Adres bilgisi bulunamadı.' }}<br>
                                {{ $order->shippingAddress?->state }} / {{ $order->shippingAddress?->city }}
                                {{ $order->shippingAddress?->zip }}
                            </p>
                        </div>
                        <div class="p-3.5 border border-black/10 bg-gray-50/50">
                            <div class="flex items-center gap-2 mb-1.5">
                                <span class="inline-flex items-center justify-center text-base text-slate-600">
                                    <i class="ri-mail-line"></i>
                                </span>
                                <div>
                                    <p class="text-[11px] uppercase tracking-wide text-gray-500">Fatura Adresi</p>
                                    <p class="text-xs font-semibold text-gray-900">
                                        {{ $order->billingAddress?->fullname ?? 'Belirtilmemiş' }}</p>
                                </div>
                            </div>
                            <p class="text-xs text-gray-600 leading-relaxed">
                                {{ $order->billingAddress?->address ?? 'Adres bilgisi bulunamadı.' }}<br>
                                {{ $order->billingAddress?->state }} / {{ $order->billingAddress?->city }}
                                {{ $order->billingAddress?->zip }}
                            </p>
                        </div>
                    </div>
                    @if ($order->status !== 'canceled')
                        <form action="{{ route('returns.store') }}" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="order_id"
                                value="{{ auth()->check() && $order->user_id === auth()->id() ? $order->id : '' }}">
                            <input type="hidden" name="order_number" value="{{ $order->order_number }}">
                            <input type="hidden" name="email"
                                value="{{ request('email') ?? ($order->shippingAddress?->email ?? auth()->user()?->email) }}">
                            <div class="space-y-2.5">
                                <p class="text-xs font-semibold text-gray-700">İade etmek istediğiniz ürünleri seçin</p>
                                @error('items')
                                    <p class="text-[11px] text-red-600">{{ $message }}</p>
                                @enderror
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                    @foreach ($order->items as $item)
                                        @php
                                            $isReturned = in_array($item->id, $returnedItemIds, true);
                                            $isChecked = in_array($item->id, $selectedItems, true);
                                        @endphp
                                        <label class="return-product-card {{ $isReturned ? 'is-disabled' : '' }}"
                                            data-return-card>
                                            <input type="checkbox" name="items[]" value="{{ $item->id }}"
                                                class="sr-only" @checked($isChecked)
                                                {{ $isReturned ? 'disabled' : '' }}>
                                            <span class="return-card-thumb">
                                                @if ($item->product?->photo)
                                                    <img src="{{ asset($item->product->photo) }}"
                                                        alt="{{ $item->product?->title }}"
                                                        class="w-full h-full object-cover">
                                                @else
                                                    <i class="ri-image-line text-xl text-gray-400"></i>
                                                @endif
                                            </span>
                                            <span class="return-card-info">
                                                <span class="return-card-title">
                                                    {{ Str::limit($item->product?->title ?? 'Ürün', 42) }}
                                                </span>
                                                <span class="return-card-price">
                                                    {{ number_format((float) $item->total, 2, ',', '.') }} ₺
                                                </span>
                                            </span>
                                            @if ($isReturned)
                                                <span class="return-card-badge">İade talebi mevcut</span>
                                            @else
                                                <span class="return-check">
                                                    <i class="ri-check-line"></i>
                                                </span>
                                            @endif
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700">İade Sebebi</label>
                                    <textarea name="reason" rows="3"
                                        class="mt-1.5 w-full px-3.5 py-2 border border-black/10 text-sm focus:border-blue-500 focus:ring-0"
                                        placeholder="İade sebebinizi paylaşın...">{{ old('reason') }}</textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700">Ek Not</label>
                                    <textarea name="notes" rows="3"
                                        class="mt-1.5 w-full px-3.5 py-2 border border-black/10 text-sm focus:border-blue-500 focus:ring-0"
                                        placeholder="Kurye, ürün durumu vb. bilgileri iletebilirsiniz.">{{ old('notes') }}</textarea>
                                </div>
                            </div>
                            <button type="submit"
                                class="w-full md:w-auto px-5 py-2.5 {{ $theme->color ? 'bg-' . $theme->color : 'bg-gray-900' }} text-white text-xs font-semibold hover:{{ $theme->color ? 'bg-' . $theme->color . '/30' : 'bg-black' }} transition-colors">
                                İade Talebi Oluştur
                            </button>
                        </form>
                    @else
                        <div class="p-3 bg-red-50 border border-red-100 text-red-800 text-xs">
                            <i class="ri-error-warning-line mr-1.5"></i>
                            İptal edilmiş siparişler için iade talebi oluşturulamaz.
                        </div>
                    @endif
                    @if ($returns->isNotEmpty())
                        <div class="border border-black/10 p-3.5">
                            <h3 class="text-xs font-semibold text-gray-900 mb-2.5">İade Talepleriniz</h3>
                            <div class="space-y-2.5 text-xs">
                                @foreach ($returns as $return)
                                    <div
                                        class="flex items-center justify-between p-2.5 bg-gray-50/50 border border-black/5">
                                        <div>
                                            <p class="font-semibold text-gray-900">
                                                {{ $return->created_at?->format('d.m.Y H:i') }} tarihinde oluşturuldu
                                            </p>
                                            <p class="text-[11px] text-gray-500 mt-0.5">
                                                {{ Str::limit($return->reason, 80) }}</p>
                                        </div>
                                        <span
                                            class="text-[11px] font-semibold px-2.5 py-0.5
                                            @if ($return->status === 'pending') bg-blue-100 text-blue-700
                                            @elseif($return->status === 'completed') bg-green-100 text-green-700
                                            @else bg-red-100 text-red-700 @endif">
                                            @php
                                                $returnStatus = [
                                                    'pending' => 'İade Sürecinde',
                                                    'completed' => 'İade Edildi',
                                                    'rejected' => 'Reddedildi',
                                                ];
                                            @endphp
                                            {{ $returnStatus[$return->status] ?? ucfirst($return->status) }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @elseif($data->orders && $data->orders->isNotEmpty())
                <div class="bg-white border border-black/10 shadow-sm overflow-hidden">
                    <div class="px-4 py-3 border-b border-black/10">
                        <h3 class="text-base font-semibold text-gray-900">Eşleşen Siparişler</h3>
                        <p class="text-xs text-gray-500">E-posta adresinizle eşleşen siparişler aşağıda listelenmiştir.</p>
                    </div>
                    <div class="divide-y divide-black/5">
                        @foreach ($data->orders as $item)
                            <a href="{{ route('returns.lookup', ['order_number' => $item->order_number, 'email' => $item->shippingAddress?->email ?? $item->user?->email]) }}"
                                class="flex items-center justify-between px-4 py-3 hover:bg-gray-50/50 transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center justify-center text-xl text-blue-600">
                                        <i class="ri-file-list-3-line"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">#{{ $item->order_number }}</p>
                                        <p class="text-[11px] text-gray-500">{{ $item->created_at->format('d.m.Y H:i') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-gray-900">
                                        {{ number_format($item->total, 2, ',', '.') }} ₺</p>
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-blue-600">Detayları Gör
                                        →</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @elseif(request()->filled('order_number') || request()->filled('email'))
                <div class="bg-red-50 border border-red-100 p-4 text-xs text-red-700">
                    Girdiğiniz bilgilerle eşleşen bir sipariş bulunamadı. Lütfen bilgilerinizi kontrol edin.
                </div>
            @endif
        </div>
    </section>
@endsection
