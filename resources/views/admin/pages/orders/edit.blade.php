@extends('admin.layouts.app')

@section('title', 'Sipariş Düzenle')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-lg font-semibold">Sipariş Düzenle</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Siparişe ait teslimat ve fatura adreslerini
                düzenleyebilirsiniz.</p>
        </div>
        <a href="{{ route('admin.orders.show', $order->id) }}"
            class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
            <i class="ri-arrow-left-line"></i>
            <span>Detay Sayfasına Dön</span>
        </a>
    </div>

    <form id="order-address-form" class="space-y-6" data-url="{{ route('admin.orders.update', $order->id) }}">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm p-5 bg-white dark:bg-black">
                <h2 class="text-base font-semibold mb-4 flex items-center gap-2">
                    <i class="ri-truck-line"></i>
                    <span>Teslimat Bilgileri</span>
                </h2>
                <div class="space-y-4">
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Ad
                            Soyad</label>
                        <input type="text" name="shipping_fullname"
                            value="{{ $order->shippingAddress->fullname ?? $order->user?->name }}"
                            class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                    </div>
                    <div>
                        <label
                            class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Telefon
                            Numarası</label>
                        <input type="text" name="shipping_phone"
                            value="{{ $order->shippingAddress->phone ?? $order->billingAddress->phone }}"
                            class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">TC
                            Kimlik No</label>
                        <input type="text" name="shipping_tc" value="{{ $order->shippingAddress->tc ?? '' }}"
                            class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label
                                class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Şehir</label>
                            <input type="text" name="shipping_city" value="{{ $order->shippingAddress->city ?? '' }}"
                                class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                        </div>
                        <div>
                            <label
                                class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">İlçe</label>
                            <input type="text" name="shipping_state" value="{{ $order->shippingAddress->state ?? '' }}"
                                class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Posta
                            Kodu</label>
                        <input type="text" name="shipping_zip" value="{{ $order->shippingAddress->zip ?? '' }}"
                            class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                    </div>
                    <div>
                        <label
                            class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Adres</label>
                        <textarea name="shipping_address" rows="4"
                            class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">{{ $order->shippingAddress->address ?? $order->shipping_address }}</textarea>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm p-5 bg-white dark:bg-black">
                <h2 class="text-base font-semibold mb-4 flex items-center gap-2">
                    <i class="ri-bill-line"></i>
                    <span>Fatura Bilgileri</span>
                </h2>
                <div class="space-y-4">
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Ad
                            Soyad</label>
                        <input type="text" name="billing_fullname"
                            value="{{ $order->billingAddress->fullname ?? $order->user?->name }}"
                            class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                    </div>
                    <div>
                        <label
                            class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Telefon
                            Numarası</label>
                        <input type="text" name="billing_phone"
                            value="{{ $order->billingAddress->phone ?? $order->shippingAddress->phone }}"
                            class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">TC
                            Kimlik No</label>
                        <input type="text" name="billing_tc" value="{{ $order->billingAddress->tc ?? '' }}"
                            class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label
                                class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Şehir</label>
                            <input type="text" name="billing_city" value="{{ $order->billingAddress->city ?? '' }}"
                                class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                        </div>
                        <div>
                            <label
                                class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">İlçe</label>
                            <input type="text" name="billing_state" value="{{ $order->billingAddress->state ?? '' }}"
                                class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Posta
                            Kodu</label>
                        <input type="text" name="billing_zip" value="{{ $order->billingAddress->zip ?? '' }}"
                            class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                    </div>
                    <div>
                        <label
                            class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Adres</label>
                        <textarea name="billing_address" rows="4"
                            class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">{{ $order->billingAddress->address ?? $order->billing_address }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-2">
            <a href="{{ url()->previous() }}"
                class="px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800">Vazgeç</a>
            <button type="submit"
                class="px-3 py-2 rounded-md text-sm border border-gray-200 dark:border-gray-800 bg-black text-white dark:bg-white dark:text-black">Kaydet</button>
        </div>
    </form>

    @push('scripts')
        <script>
            $('#order-address-form').on('submit', function(e) {
                e.preventDefault();
                const $form = $(this);
                const url = $form.data('url');
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: $form.serialize(),
                    success: function(res) {
                        showSuccess(res?.msg || 'Sipariş adresleri güncellendi');
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON?.msg || 'Hata oluştu';
                        showError(msg);
                    }
                });
            });
        </script>
    @endpush
@endsection
