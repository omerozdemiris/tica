<footer class="bg-white border-t border-gray-100 mt-24">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-16 border-b border-gray-50">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
            <div class="flex flex-col items-center text-center group">
                <div
                    class="w-24 h-24 md:w-32 md:h-32 rounded-full bg-[#E8F3F4] flex items-center justify-center mb-6 transition-transform group-hover:scale-110">
                    <img src="{{ asset('assets/img/benefit-1.webp') }}" alt="Teslimat"
                        class="w-full h-full object-contain">
                </div>
                <h5 class="text-[14px] font-bold text-black mb-2 uppercase tracking-wide">3 Farklı Teslim Seçeneği</h5>
                <p class="text-[12px] text-gray-500">Kapıda Teslim - Kargo - Kendin Al</p>
            </div>
            <div class="flex flex-col items-center text-center group">
                <div
                    class="w-24 h-24 md:w-32 md:h-32 rounded-full bg-[#EAF5EC] flex items-center justify-center mb-6 transition-transform group-hover:scale-110">
                    <img src="{{ asset('assets/img/benefit-2.webp') }}" alt="Üreticiden"
                        class="w-full h-full object-contain">
                </div>
                <h5 class="text-[14px] font-bold text-black mb-2 uppercase tracking-wide">Üreticiden Kapınıza</h5>
                <p class="text-[12px] text-gray-500">En Taze Haliyle Kapınızda</p>
            </div>
            <div class="flex flex-col items-center text-center group">
                <div
                    class="w-24 h-24 md:w-32 md:h-32 rounded-full bg-[#F5F5F5] flex items-center justify-center mb-6 transition-transform group-hover:scale-110">
                    <img src="{{ asset('assets/img/benefit-3.webp') }}" alt="Sıfır Risk"
                        class="w-full h-full object-contain">
                </div>
                <h5 class="text-[14px] font-bold text-black mb-2 uppercase tracking-wide">Sıfır Risk</h5>
                <p class="text-[12px] text-gray-500">Mutlu Müşteriler</p>
            </div>
            <div class="flex flex-col items-center text-center group">
                <div
                    class="w-24 h-24 md:w-32 md:h-32 rounded-full bg-[#F7EEEE] flex items-center justify-center mb-6 transition-transform group-hover:scale-110">
                    <img src="{{ asset('assets/img/benefit-4.webp') }}" alt="Güvenli Alışveriş"
                        class="w-full h-full object-contain">
                </div>
                <h5 class="text-[14px] font-bold text-black mb-2 uppercase tracking-wide">Güvenli Alışveriş</h5>
                <p class="text-[12px] text-gray-500">Önceliğimiz Daima Güvenlik</p>
            </div>
        </div>
    </div>
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12">
            <div>
                <h4 class="text-[15px] font-bold text-black mb-3">Kurumsal</h4>
                <ul class="space-y-2">
                    @php
                        $links = [
                            ['route' => 'pages.about', 'label' => 'Hakkımızda'],
                            ['route' => 'tel:' . $settings->phone, 'label' => 'Müşteri Hizmetleri'],
                            ['route' => 'pages.distance-selling', 'label' => 'Satış Sözleşmesi'],
                            ['route' => 'pages.privacy', 'label' => 'Gizlilik Politikası'],
                            ['route' => 'pages.cookies', 'label' => 'Çerez Politikası'],
                        ];
                    @endphp
                    @foreach ($links as $link)
                        <li>
                            @if ($link['route'] == 'tel:' . $settings->phone)
                                <a href="tel:{{ $settings->phone }}"
                                    class="text-[13px] text-gray-600 hover:text-{{ $theme->color ? $theme->color : 'text-blue-600' }} transition-colors">
                                    {{ $link['label'] }}
                                </a>
                            @else
                                <a href="{{ Route::has($link['route']) ? route($link['route']) : '#' }}"
                                    class="text-[13px] text-gray-600 hover:text-{{ $theme->color ? $theme->color : 'text-blue-600' }} transition-colors">
                                    {{ $link['label'] }}
                                </a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
            <div>
                <h4 class="text-[15px] font-bold text-black mb-3">Alışveriş</h4>
                <ul class="space-y-2">
                    <li><a href="{{ route('login') }}"
                            class="text-[13px] text-gray-600 hover:text-{{ $theme->color ? $theme->color : 'text-blue-600' }}">Üyelik</a>
                    </li>
                    <li><a href="{{ route('returns.lookup') }}"
                            class="text-[13px] text-gray-600 hover:text-{{ $theme->color ? $theme->color : 'text-blue-600' }}">Sipariş
                            Takibi</a>
                    </li>
                    <li><a href="{{ route('products.index') }}"
                            class="text-[13px] text-gray-600 hover:text-{{ $theme->color ? $theme->color : 'text-blue-600' }}">Güncel
                            Katalog</a>
                    </li>
                </ul>
            </div>
            <div>
                <h4 class="text-[15px] font-bold text-black mb-3">Kategoriler</h4>
                <ul class="space-y-2">
                    @foreach (($categories ?? collect())->whereNull('category_id')->take(8) as $category)
                        <li>
                            <a href="{{ route('categories.show', [$category->id, $category->slug]) }}"
                                class="text-[13px] text-gray-600 hover:text-{{ $theme->color ? $theme->color : 'text-blue-600' }} transition-colors">
                                {{ $category->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div>
                <h4 class="text-[15px] font-bold text-black mb-3">İletişim</h4>
                <div class="space-y-3">
                    @if (!empty($settings?->phone))
                        <div class="flex flex-col">
                            <span class="text-[11px] text-gray-400 uppercase font-bold mb-1">Müşteri Destek</span>
                            <a href="tel:{{ $settings->phone }}"
                                class="text-[18px] font-bold text-black">{{ $settings->phone }}</a>
                        </div>
                    @endif

                    <div class="flex items-center gap-4 pt-4">
                        @php $socials = ['facebook', 'twitter', 'instagram', 'youtube', 'linkedin']; @endphp
                        @foreach ($socials as $social)
                            @if (!empty($settings->$social))
                                <a href="{{ $settings->$social }}" target="_blank"
                                    class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center text-gray-600 hover:bg-{{ $theme->color ? $theme->color : 'bg-blue-600' }} hover:text-white transition-all">
                                    <i class="ri-{{ $social === 'twitter' ? 'twitter-x' : $social }}-line"></i>
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="border-t border-gray-50 py-10 bg-gray-50">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 flex flex-col items-center gap-6">
            @if ($store->tax_enabled == 0)
                <p class="text-gray-500 text-xs font-bold">Tüm Fiyatlarımızda KDV Dahildir.</p>
            @endif
            <div class="opacity-80">
                <img src="{{ asset('assets/img/payment.webp') }}" alt="Ödeme Yöntemleri" class="h-5 w-auto">
            </div>
            <div class="flex flex-col items-center gap-6">
                <a href="{{ route('home') }}">
                    @if (!empty($settings?->logo))
                        <img src="{{ $settings->logo }}" alt="{{ $settings->title }}"
                            class="h-8 w-auto grayscale opacity-50 hover:grayscale-0 hover:opacity-100 transition-all">
                    @else
                        <span class="text-xl font-black uppercase text-gray-300">{{ $settings->title }}</span>
                    @endif
                </a>
                <a href="https://macroturk.com" target="_blank" class="text-[11px] font-bold text-gray-400 uppercase tracking-widest flex flex-col items-center">
                    &copy; {{ date('Y') }} MacroTurk Yazılım. TÜM HAKLARI SAKLIDIR. <span><img src="/assets/img/macroturklogo.svg" class="w-24 mt-3 object-contain"></span>
                </a>
            </div>
        </div>
    </div>
</footer>
