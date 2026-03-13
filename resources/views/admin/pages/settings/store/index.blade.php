@extends('admin.layouts.app')
@section('title', 'Mağaza Ayarları')
@section('content')
    <div class="flex flex-col md:flex-row gap-8 relative items-start">
        <div class="w-full md:w-64 md:sticky md:top-24 space-y-1">
            <h1 class="text-xl flex items-center gap-2 font-bold mb-6 px-3"><span><i
                        class="ri-store-line text-lg font-normal"></i></span>Mağaza Ayarları</h1>
            <nav id="settings-nav" class="flex flex-col gap-1">
                <a href="#section-purchase"
                    class="nav-link px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 border border-transparent hover:bg-gray-100 dark:hover:bg-gray-800 flex items-center gap-3 active-nav"
                    data-section="section-purchase">
                    <i class="ri-shopping-basket-line text-lg"></i>
                    Satın Alma
                </a>
                <a href="#section-notifications"
                    class="nav-link px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 border border-transparent hover:bg-gray-100 dark:hover:bg-gray-800 flex items-center gap-3"
                    data-section="section-notifications">
                    <i class="ri-notification-line text-lg"></i>
                    Bildirimler
                </a>
                <a href="#section-banks"
                    class="nav-link px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 border border-transparent hover:bg-gray-100 dark:hover:bg-gray-800 flex items-center gap-3"
                    data-section="section-banks">
                    <i class="ri-funds-line text-lg"></i>
                    Banka Bilgileri
                </a>
                <a href="#section-meta"
                    class="nav-link px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 border border-transparent hover:bg-gray-100 dark:hover:bg-gray-800 flex items-center gap-3"
                    data-section="section-meta">
                    <i class="ri-search-eye-line text-lg"></i>
                    SEO (Meta)
                </a>
                <a href="#section-legal"
                    class="nav-link px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 border border-transparent hover:bg-gray-100 dark:hover:bg-gray-800 flex items-center gap-3"
                    data-section="section-legal">
                    <i class="ri-shield-check-line text-lg"></i>
                    Yasal Bilgiler
                </a>
            </nav>

            <div class="py-2 mx-2 border-t border-dashed border-gray-300 dark:border-gray-800"></div>

            <div class="pt-2 px-3">
                <button form="store-settings-form" type="submit"
                    class="w-full px-4 py-2.5 rounded-lg text-sm font-semibold bg-black text-white dark:bg-white dark:text-black hover:opacity-90 transition-opacity flex items-center justify-center gap-2">
                    <i class="ri-save-line"></i>
                    Ayarları Güncelle
                </button>
            </div>
        </div>
        <div class="flex-1 w-full max-w-5xl">
            <form id="store-settings-form" class="space-y-12 pb-24">
                @csrf
                @method('PUT')
                <section id="section-purchase" class="scroll-mt-24 pb-20">
                    <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-black/20 p-6">
                        <h2 class="text-lg font-bold mb-2 flex items-center gap-2">
                            <i class="ri-shopping-basket-line"></i> Satın Alma Ayarları
                        </h2>
                        <div class="text-xs text-gray-500 mb-6 flex items-start gap-2">
                            <span class="mt-0.5"><i class="ri-information-line"></i></span>
                            <p>Mağazanızın satış süreçlerini buradan yönetebilirsiniz. Ürünlerin satışa açık olma durumunu,
                                üyelik gereksinimlerini ve kargo ücreti gibi temel ticari ayarları bu bölümden
                                yapılandırabilirsiniz.</p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <label
                                class="flex flex-col group p-3 rounded-lg border border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-sm font-semibold">Ürünler Satın Almaya Açık</span>
                                    <input type="checkbox" name="sell_enabled" value="1"
                                        @if ($storeSettings->sell_enabled ?? 0) checked @endif class="toggle">
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Ürünleriniz satışa hazırsa
                                    açabilirsiniz.</p>
                            </label>

                            <label
                                class="flex flex-col group p-3 rounded-lg border border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-sm font-semibold">Üye Girişi Zorunlu</span>
                                    <input type="checkbox" name="auth_required" value="1"
                                        @if ($storeSettings->auth_required ?? 0) checked @endif class="toggle">
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Üye olmadan satın alma için kapalı
                                    tutun.</p>
                            </label>

                            <label
                                class="flex flex-col group p-3 rounded-lg border border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-sm font-semibold">Site Bakım Modu</span>
                                    <input type="checkbox" name="maintenance" value="1"
                                        @if ($storeSettings->maintenance ?? 0) checked @endif class="toggle">
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Siteyi geçici olarak ziyaretçilere
                                    kapatır.</p>
                            </label>

                            <label
                                class="flex flex-col group p-3 rounded-lg border border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-sm font-semibold">Stok Takibi</span>
                                    <input type="checkbox" name="auto_stock" value="1"
                                        @if ($storeSettings->auto_stock ?? 0) checked @endif class="toggle">
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Satışlarda ürün stoklarının
                                    takibini sağlar.</p>
                            </label>

                            <label
                                class="flex flex-col group p-3 rounded-lg border border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-sm font-semibold">Tüm Kategorileri Önde Göster</span>
                                    <input type="checkbox" name="show_categories" value="1"
                                        @if ($storeSettings->show_categories ?? false) checked @endif class="toggle">
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Ana sayfada tüm kategorileri
                                    sekmeler halinde gösterir.</p>
                            </label>

                            <label
                                class="flex flex-col group p-3 rounded-lg border border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-sm font-semibold">Sipariş Bilgilendirme Maili</span>
                                    <input type="checkbox" name="notify_order_complete" value="1"
                                        @if ($storeSettings->notify_order_complete ?? 1) checked @endif class="toggle">
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Sipariş tamamlandığında yöneticiye
                                    mail gönderir.</p>
                            </label>

                            <label
                                class="flex flex-col group p-3 rounded-lg border border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-sm font-semibold">E-posta Doğrulama Zorunlu</span>
                                    <input type="checkbox" name="verify_required" value="1"
                                        @if ($storeSettings->verify_required ?? 0) checked @endif class="toggle">
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Üyelerin doğrulanmış mail ile
                                    girişini zorunlu kılar.</p>
                            </label>

                            <label
                                class="flex flex-col group p-3 rounded-lg border border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-sm font-semibold">Telefon Numarası Zorunlu</span>
                                    <input type="checkbox" name="phone_required" value="1"
                                        @if ($storeSettings->phone_required ?? 0) checked @endif class="toggle">
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Üyelerin telefon numarasının
                                    zorunlu olduğunu belirler.</p>
                            </label>

                            <label
                                class="flex flex-col group p-3 rounded-lg border border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-sm font-semibold">Adreslerde TC Kimlik Numarası Zorunlu</span>
                                    <input type="checkbox" name="tc_required" value="1"
                                        @if ($storeSettings->tc_required ?? 0) checked @endif class="toggle">
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Üyelerin adres bilgilerini tc
                                    kimlik numarası ile doğrulamalarını zorunlu kılar.</p>
                            </label>

                            <label
                                class="flex flex-col group p-3 rounded-lg border border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-sm font-semibold">Havale / EFT ile ödeme</span>
                                    <input type="checkbox" name="allow_wire_payments" value="1"
                                        @if ($storeSettings->allow_wire_payments ?? false) checked @endif class="toggle">
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Ödeme aşamasında banka havalesi
                                    seçeneğini aktif eder.</p>
                            </label>

                            <label
                                class="flex flex-col group p-3 rounded-lg border border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-sm font-semibold">Vergi Hesaplamaları</span>
                                    <input type="checkbox" name="tax_enabled" value="1"
                                        @if ($storeSettings->tax_enabled ?? 0) checked @endif class="toggle" data-tax-toggle>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Fiyatlarınıza vergi dahil değilse
                                    bu seçeneği açın.</p>
                            </label>
                        </div>

                        <div
                            class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-100 dark:border-gray-800">
                            <div class="space-y-1" data-tax-rate-wrap>
                                <label class="text-sm font-medium">Vergi Oranı (%)</label>
                                <input type="number" name="tax_rate" step="0.01" min="0"
                                    value="{{ $storeSettings->tax_rate ?? '' }}"
                                    class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-black focus:ring-2 focus:ring-black dark:focus:ring-white transition-all outline-none">
                                <p class="text-xs text-gray-500 mt-1">Örn: 20</p>
                            </div>
                            <div class="space-y-1">
                                <label class="text-sm font-medium">Kargo Ücreti</label>
                                <input type="number" name="shipping_price" step="0.01" min="0"
                                    value="{{ $storeSettings->shipping_price ?? '' }}"
                                    class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-black focus:ring-2 focus:ring-black dark:focus:ring-white transition-all outline-none">
                                <p class="text-xs text-gray-500 mt-1">Limit altı siparişler için kargo bedeli.</p>
                            </div>
                            <div class="space-y-1">
                                <label class="text-sm font-medium">Kargo Ücreti Limit</label>
                                <input type="number" name="shipping_price_limit" step="0.01" min="0"
                                    value="{{ $storeSettings->shipping_price_limit ?? '' }}"
                                    class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-black focus:ring-2 focus:ring-black dark:focus:ring-white transition-all outline-none">
                                <p class="text-xs text-gray-500 mt-1">Ücretsiz kargo için minimum tutar.</p>
                            </div>
                        </div>
                    </div>
                </section>
                <section id="section-notifications" class="scroll-mt-24 pb-20">
                    <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-black/20 p-6">
                        <h2 class="text-lg font-bold mb-2 flex items-center gap-2">
                            <i class="ri-notification-line"></i> Bildirim Ayarları
                        </h2>
                        <div class="text-xs text-gray-500 mb-6 flex items-start gap-2">
                            <span class="mt-0.5"><i class="ri-information-line"></i></span>
                            <p>Müşterilerinize gönderilecek otomatik bildirimleri buradan kontrol edin. Fiyat düşüşleri,
                                stok güncellemeleri ve sepet hatırlatmaları gibi etkileşim araçlarını bu bölümden
                                yönetebilirsiniz.</p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <label
                                class="flex flex-col group p-4 rounded-lg border border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-sm font-semibold">Fiyat Bildirimi</span>
                                    <input type="checkbox" name="price_notification" value="1"
                                        @if ($storeSettings->price_notification ?? 0) checked @endif class="toggle">
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Ürün fiyatları düştüğünde
                                    kullanıcılara bildirim gönderir.</p>
                            </label>

                            <label
                                class="flex flex-col group p-4 rounded-lg border border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-sm font-semibold">Stok Bildirimi</span>
                                    <input type="checkbox" name="stock_notification" value="1"
                                        @if ($storeSettings->stock_notification ?? 0) checked @endif class="toggle">
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Ürün stokları yenilendiğinde
                                    kullanıcılara bildirim gönderir.</p>
                            </label>
                        </div>

                        <div class="mt-8 pt-8 border-t border-gray-100 dark:border-gray-800 space-y-6">
                            <label
                                class="flex items-start justify-between gap-4 border border-gray-100 hover:bg-gray-50 dark:border-gray-800 rounded-lg p-4 max-w-sm">
                                <div>
                                    <span class="text-sm font-semibold">Sepet Hatırlatma</span>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Sepetteki ürünler için hatırlatma
                                        bildirimi gönderir.</p>
                                </div>
                                <input type="checkbox" name="cart_reminder" value="1"
                                    @if ($storeSettings->cart_reminder ?? 0) checked @endif class="toggle" data-cart-reminder>
                            </label>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-1">
                                    <label class="text-sm font-medium">Hatırlatma Süresi (Saat)</label>
                                    <input type="number" name="cart_remind_time" step="1" min="1"
                                        data-cart-remind-time value="{{ $storeSettings->cart_remind_time ?? '' }}"
                                        class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-black focus:ring-2 focus:ring-black dark:focus:ring-white transition-all outline-none">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-sm font-medium">Hatırlatma Mesajı</label>
                                    <input type="text" name="cart_remind_message" data-cart-remind-message
                                        value="{{ $storeSettings->cart_remind_message ?? '' }}"
                                        placeholder="Sepetinizdeki ürünleri unutmayın!"
                                        class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-black focus:ring-2 focus:ring-black dark:focus:ring-white transition-all outline-none">
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <section id="section-banks" class="scroll-mt-24 pb-20">
                    <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-black/20 p-6">
                        <div class="flex items-center justify-between mb-2">
                            <h2 class="text-lg font-bold flex items-center gap-2">
                                <i class="ri-funds-line"></i> Havale / EFT Bankaları
                            </h2>
                            <button type="button" data-bank-modal-open
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold bg-black text-white dark:bg-white dark:text-black hover:opacity-90 transition-opacity">
                                <i class="ri-add-line"></i>
                                Banka Ekle
                            </button>
                        </div>
                        <div class="text-xs text-gray-500 mb-8 flex items-start gap-2">
                            <span class="mt-0.5"><i class="ri-information-line"></i></span>
                            <p>Havale ve EFT ile ödeme kabul etmek için banka hesap bilgilerinizi buraya ekleyin.
                                Eklediğiniz bankalar ödeme aşamasında müşterilerinize seçenek olarak sunulacaktır.</p>
                        </div>

                        <div class="space-y-4" id="bank-list">
                            @forelse ($banks as $bank)
                                <div
                                    class="p-5 rounded-2xl border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50 flex flex-wrap items-center justify-between gap-4">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="h-12 w-12 rounded-xl bg-white dark:bg-black border border-gray-200 dark:border-gray-800 flex items-center justify-center">
                                            <i class="ri-funds-line text-2xl text-gray-400"></i>
                                        </div>
                                        <div>
                                            <p class="text-base font-bold text-gray-900 dark:text-white">
                                                {{ $bank->bank_name }}</p>
                                            <p class="text-sm text-gray-600 dark:text-gray-300">{{ $bank->bank_receiver }}
                                            </p>
                                            <p class="text-xs font-mono text-gray-400 mt-1 tracking-wider">
                                                {{ $bank->bank_iban }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <label class="flex items-center gap-2 text-xs font-bold text-gray-500 mr-2">
                                            AKTİF
                                            <input type="checkbox" class="toggle toggle-sm" data-bank-status
                                                data-bank-id="{{ $bank->id }}" value="1"
                                                @checked($bank->status)>
                                        </label>
                                        <button type="button" data-bank-edit data-bank-id="{{ $bank->id }}"
                                            data-bank-name="{{ $bank->bank_name }}"
                                            data-bank-iban="{{ $bank->bank_iban }}"
                                            data-bank-receiver="{{ $bank->bank_receiver }}"
                                            data-bank-status="{{ $bank->status ? 1 : 0 }}"
                                            class="p-2 rounded-lg border border-gray-200 dark:border-gray-800 text-gray-600 dark:text-gray-400 hover:bg-white dark:hover:bg-black transition-colors">
                                            <i class="ri-pencil-line"></i>
                                        </button>
                                        <button type="button" data-bank-delete data-bank-id="{{ $bank->id }}"
                                            class="p-2 rounded-lg border border-red-100 text-red-500 hover:bg-red-50 transition-colors">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div
                                    class="p-8 rounded-2xl border border-dashed border-gray-200 dark:border-gray-800 text-center">
                                    <i class="ri-funds-line text-4xl text-gray-300 mb-3 block"></i>
                                    <p class="text-sm text-gray-500">Henüz banka bilgisi eklenmemiş.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </section>

                <section id="section-meta" class="scroll-mt-24 pb-20">
                    <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-black/20 p-6">
                        <h2 class="text-lg font-bold mb-2">
                            <i class="ri-search-eye-line"></i> SEO (Meta) Ayarları
                        </h2>
                        <div class="text-xs text-gray-500 mb-6 flex items-start gap-2">
                            <span class="mt-0.5"><i class="ri-information-line"></i></span>
                            <p>Mağazanızın arama motorlarında nasıl görüneceğini buradan belirleyin. Doğru başlık ve
                                açıklamalar kullanarak mağazanızın SEO performansını ve tıklanma oranlarını
                                artırabilirsiniz. <strong>Meta</strong> ve <strong>Google</strong> alanlarına ilgili
                                entegrasyon hesaplarınızdaki kimlik ve
                                anahtar verilerini girmeniz yeterli.</p>
                        </div>
                        <div class="grid grid-cols-1 gap-6">
                            <div class="space-y-1 flex items-center gap-4">
                                <i class="ri-global-line text-6xl"></i>
                                <div class="flex flex-col w-full">
                                    <label class="text-sm font-medium mb-2">
                                        Meta Başlığı
                                    </label>
                                    <input type="text" name="meta_title"
                                        value="{{ $storeSettings->meta_title ?? '' }}"
                                        class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-black focus:ring-2 focus:ring-black dark:focus:ring-white transition-all outline-none">

                                </div>
                            </div>
                            <div class="space-y-1 flex items-center gap-4">
                                <i class="ri-svelte-line text-6xl"></i>
                                <div class="flex flex-col w-full">
                                    <label class="text-sm font-medium mb-2">
                                        Meta Açıklaması
                                    </label>
                                    <input name="meta_description" rows="3" value="{!! strip_tags($storeSettings->meta_description ?? '') !!}"
                                        class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-black focus:ring-2 focus:ring-black dark:focus:ring-white transition-all outline-none">

                                </div>
                            </div>
                            <div class="space-y-1 flex items-center gap-4">
                                <img src="{{ asset('assets/admin/assets/img/meta.svg') }}" alt="Meta"
                                    class="w-14 h-14">
                                <div class="flex flex-col w-full">
                                    <label class="text-sm font-medium mb-2">
                                        Facebook Meta Kodu ID'si
                                    </label>
                                    <input name="facebook_meta_code" type="text" name="facebook_meta_code"
                                        value="{{ $storeSettings->facebook_meta_code ?? '' }}"
                                        class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-black focus:ring-2 focus:ring-black dark:focus:ring-white transition-all outline-none">
                                </div>
                            </div>
                            <div class="space-y-1 flex items-center gap-4">
                                <img src="{{ asset('assets/admin/assets/img/tag.svg') }}" alt="Google Tag Manager"
                                    class="w-14 h-14">
                                <div class="flex flex-col w-full">
                                    <label class="text-sm font-medium mb-2">
                                        Google Tag Manager ID'si
                                    </label>
                                    <input name="google_tag_manager" type="text" name="google_tag_manager"
                                        value="{{ $storeSettings->google_tag_manager ?? '' }}"
                                        class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-black focus:ring-2 focus:ring-black dark:focus:ring-white transition-all outline-none">

                                </div>
                            </div>
                            <div class="space-y-1 flex items-center gap-4">
                                <img src="{{ asset('assets/admin/assets/img/ads.svg') }}" alt="Google Ads"
                                    class="w-14 h-14">
                                <div class="flex flex-col w-full">
                                    <label class="text-sm font-medium mb-2">
                                        Google Ads ID'si
                                    </label>
                                    <input name="google_ads" type="text" name="google_ads"
                                        value="{{ $storeSettings->google_ads ?? '' }}"
                                        class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-black focus:ring-2 focus:ring-black dark:focus:ring-white transition-all outline-none">

                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <section id="section-legal" class="scroll-mt-24 pb-20">
                    <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-black/20 p-6">
                        <h2 class="text-lg font-bold mb-2 flex items-center gap-2">
                            <i class="ri-shield-check-line"></i> Mağaza Yasal Bilgileri
                        </h2>
                        <div class="text-xs text-gray-500 mb-6 flex items-start gap-2">
                            <span class="mt-0.5"><i class="ri-information-line"></i></span>
                            <p>Mağazanızın yasal uyumluluğu için gerekli metinleri buradan güncelleyebilirsiniz. Hakkımızda,
                                Gizlilik ve Mesafeli Satış Sözleşmesi gibi dökümanlar müşteri güveni için kritik öneme
                                sahiptir.</p>
                        </div>
                        <div class="space-y-4">
                            @php
                                $legalDocs = [
                                    ['name' => 'about', 'label' => 'Mağazam Hakkında'],
                                    ['name' => 'privacy_policy', 'label' => 'Gizlilik Politikası'],
                                    ['name' => 'cookie_policy', 'label' => 'Çerez Politikası'],
                                    ['name' => 'distance_selling', 'label' => 'Mesafeli Satış Sözleşmesi'],
                                ];
                            @endphp

                            @foreach ($legalDocs as $doc)
                                <div x-data="{ open: true }"
                                    class="border border-gray-100 dark:border-gray-800 rounded-xl overflow-hidden bg-gray-50/30 dark:bg-gray-900/30">
                                    <button type="button" @click="open = !open"
                                        class="w-full flex items-center justify-between p-4 hover:bg-gray-100/50 dark:hover:bg-gray-800/50 transition-colors">
                                        <span class="text-sm font-bold">{{ $doc['label'] }}</span>
                                        <i class="ri-arrow-down-s-line transition-transform duration-200"
                                            :class="{ 'rotate-180': open }"></i>
                                    </button>
                                    <div x-show="open" x-transition x-cloak
                                        class="p-4 bg-white dark:bg-black border-t border-gray-100 dark:border-gray-800">
                                        <textarea name="{{ $doc['name'] }}" rows="12"
                                            class="w-full px-4 py-3 rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-black focus:ring-2 focus:ring-black dark:focus:ring-white transition-all outline-none">{{ $storeSettings->{$doc['name']} ?? '' }}</textarea>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            </form>
        </div>
    </div>
    <div id="bank-modal"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center px-4 py-6 hidden z-[100]">
        <div
            class="bg-white dark:bg-gray-900 rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden border border-gray-100 dark:border-gray-800">
            <div
                class="p-6 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between bg-gray-50/50 dark:bg-gray-900/50">
                <h3 id="bank-modal-title" class="text-lg font-bold text-gray-900 dark:text-white">Yeni Banka</h3>
                <button type="button" data-bank-modal-close
                    class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-800 transition-colors">
                    <i class="ri-close-line text-xl"></i>
                </button>
            </div>
            <form id="bank-form" class="p-8 space-y-6">
                @csrf
                <input type="hidden" name="_method" value="POST">
                <input type="hidden" name="bank_id">

                <div class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-sm font-medium">Banka Adı</label>
                        <input type="text" name="bank_name"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-black focus:ring-2 focus:ring-black dark:focus:ring-white outline-none transition-all"
                            placeholder="Örn: Ziraat Bankası">
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium">IBAN</label>
                        <input type="text" name="bank_iban"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-black focus:ring-2 focus:ring-black dark:focus:ring-white outline-none transition-all"
                            placeholder="TR00 0000...">
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium">Alıcı Adı</label>
                        <input type="text" name="bank_receiver"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-black focus:ring-2 focus:ring-black dark:focus:ring-white outline-none transition-all"
                            placeholder="Örn: ABC Ticaret A.Ş.">
                    </div>
                </div>

                <label
                    class="flex items-center gap-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-800 cursor-pointer">
                    <input type="checkbox" name="status" value="1" class="toggle" checked>
                    <span class="text-sm font-semibold">Ödeme adımında gösterilsin</span>
                </label>

                <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" data-bank-modal-close
                        class="px-6 py-2.5 rounded-xl text-sm font-bold border border-gray-200 dark:border-gray-700 hover:bg-gray-50 transition-colors">Vazgeç</button>
                    <button type="submit"
                        class="px-8 py-2.5 rounded-xl text-sm font-bold bg-black text-white dark:bg-white dark:text-black hover:opacity-90 transition-opacity">
                        Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .active-nav {
            background-color: #fff;
            color: rgb(0 0 0);
            font-weight: 600;
        }

        .dark .active-nav {
            background-color: rgb(31 41 55);
            color: rgb(255 255 255);
        }

        html {
            scroll-behavior: smooth;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>

    @push('scripts')
        <script>
            // Scroll Spy logic
            const sections = document.querySelectorAll('section');
            const navLinks = document.querySelectorAll('.nav-link');

            window.addEventListener('scroll', () => {
                let current = '';
                sections.forEach(section => {
                    const sectionTop = section.offsetTop;
                    if (window.pageYOffset >= sectionTop - 150) {
                        current = section.getAttribute('id');
                    }
                });

                navLinks.forEach(link => {
                    link.classList.remove('active-nav');
                    if (link.getAttribute('data-section') === current) {
                        link.classList.add('active-nav');
                    }
                });
            });

            // Form Submit logic
            $('#store-settings-form').on('submit', function(e) {
                e.preventDefault();
                const data = $(this).serializeArray();
                const ensure = [
                    'sell_enabled', 'auth_required', 'maintenance', 'auto_stock',
                    'tax_enabled', 'notify_order_complete', 'allow_wire_payments',
                    'show_categories', 'show_new_products', 'price_notification',
                    'stock_notification', 'cart_reminder'
                ];
                ensure.forEach(function(name) {
                    if (!data.find(x => x.name === name)) {
                        data.push({
                            name: name,
                            value: 0
                        });
                    }
                });
                $.ajax({
                    url: "{{ route('admin.store-settings.update') }}",
                    method: "POST",
                    data: $.param(data),
                    success: function(res) {
                        showSuccess(res?.msg);
                    },
                    error: function(xhr) {
                        showError(xhr.responseJSON?.msg || 'Hata');
                    }
                });
            });

            // Input Helpers
            const $taxToggle = $('[data-tax-toggle]');
            const $taxRateWrap = $('[data-tax-rate-wrap]');
            const $cartReminder = $('[data-cart-reminder]');
            const $cartInputs = $('[data-cart-remind-time], [data-cart-remind-message]');

            function syncInputs() {
                $taxRateWrap.toggleClass('opacity-50 pointer-events-none', !$taxToggle.is(':checked'));
                $cartInputs.toggleClass('opacity-50 pointer-events-none', !$cartReminder.is(':checked'));
            }
            $taxToggle.add($cartReminder).on('change', syncInputs);
            syncInputs();

            // Bank Operations
            const bankModal = $('#bank-modal');
            const bankForm = $('#bank-form');
            const bankTitle = $('#bank-modal-title');
            const bankRoutes = {
                base: "{{ url('admin/banks') }}",
                status: "{{ url('admin/banks') }}/:id/status",
            };

            function openBankModal(bank = null) {
                bankForm[0].reset();
                bankForm.find('[name="bank_id"]').val(bank?.id || '');
                bankForm.find('[name="_method"]').val(bank ? 'PUT' : 'POST');
                bankTitle.text(bank ? 'Banka Bilgisini Düzenle' : 'Yeni Banka');

                if (bank) {
                    bankForm.find('[name="bank_name"]').val(bank.bank_name);
                    bankForm.find('[name="bank_iban"]').val(bank.bank_iban);
                    bankForm.find('[name="bank_receiver"]').val(bank.bank_receiver);
                    bankForm.find('[name="status"]').prop('checked', !!bank.status);
                }
                bankModal.removeClass('hidden');
            }

            $('[data-bank-modal-open]').on('click', () => openBankModal());
            $('[data-bank-modal-close]').on('click', () => bankModal.addClass('hidden'));

            $('[data-bank-edit]').on('click', function() {
                const $btn = $(this);
                openBankModal({
                    id: $btn.data('bankId'),
                    bank_name: $btn.data('bankName'),
                    bank_iban: $btn.data('bankIban'),
                    bank_receiver: $btn.data('bankReceiver'),
                    status: $btn.data('bankStatus')
                });
            });

            bankForm.on('submit', function(e) {
                e.preventDefault();
                const bankId = bankForm.find('[name="bank_id"]').val();
                const url = bankId ? `${bankRoutes.base}/${bankId}` : bankRoutes.base;
                const payload = $(this).serializeArray();
                if (!payload.find(x => x.name === 'status')) payload.push({
                    name: 'status',
                    value: 0
                });

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: $.param(payload),
                    success: (res) => {
                        showSuccess(res?.msg);
                        window.location.reload();
                    },
                    error: (xhr) => showError(xhr.responseJSON?.msg || 'Hata')
                });
            });

            $('[data-bank-delete]').on('click', function() {
                if (!confirm('Silmek istediğinize emin misiniz?')) return;
                $.ajax({
                    url: `${bankRoutes.base}/${$(this).data('bankId')}`,
                    method: 'POST',
                    data: {
                        _method: 'DELETE',
                        _token: "{{ csrf_token() }}"
                    },
                    success: (res) => {
                        showSuccess(res?.msg);
                        window.location.reload();
                    }
                });
            });

            $('[data-bank-status]').on('change', function() {
                $.ajax({
                    url: bankRoutes.status.replace(':id', $(this).data('bankId')),
                    method: 'POST',
                    data: {
                        _method: 'PATCH',
                        status: this.checked ? 1 : 0,
                        _token: "{{ csrf_token() }}"
                    },
                    success: (res) => showSuccess(res?.msg)
                });
            });
        </script>
    @endpush
@endsection
