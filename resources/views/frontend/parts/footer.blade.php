<footer
    class="bg-white border-t {{ $theme->color ? 'border-' . $theme->color . '/5' : 'border-gray-200' }} text-gray-600 mt-16">
    @if ($footer_campaign)
        @include('frontend.parts.campaings.footer')
    @endif
    <div class="max-w-[1680px] mx-auto px-4 md:px-8 py-10 grid grid-cols-2 md:grid-cols-5 gap-8">
        <div class="col-span-2 md:col-span-1 text-center lg:text-start">
            <a href="{{ route('home') }}" class="inline-flex items-start">
                @if (!empty($settings?->logo))
                    <img src="{{ $settings->logo }}" alt="{{ $settings->title ?? config('app.name') }}"
                        class="h-20 md:h-28 w-auto object-contain">
                @else
                    <span class="text-lg font-bold">{{ $settings->title ?? config('app.name') }}</span>
                @endif
            </a>
        </div>
        <div>
            <h4 class="text-[11px] uppercase  text-gray-800 font-semibold">İletişim</h4>
            <ul class="mt-4 space-y-2 text-[10px]">
                @if (!empty($settings?->phone))
                    <li><a href="tel:{{ $settings->phone }}"
                            class="hover:text-gray-900 transition">{{ $settings->phone }}</a></li>
                @endif
                @if (!empty($settings?->email))
                    <li><a href="mailto:{{ $settings->email }}"
                            class="hover:text-gray-900 transition">{{ $settings->email }}</a></li>
                @endif
                @if (!empty($settings?->address))
                    <li class="text-gray-500">{{ $settings->address }}</li>
                @endif
            </ul>
        </div>
        <div>
            <h4 class="text-[11px] uppercase  text-gray-800 font-semibold">Yardım</h4>
            <ul class="mt-4 space-y-2 text-[10px]">
                <li><a href="{{ route('returns.lookup') }}" class="hover:text-gray-900 transition">Sipariş Sorgula</a>
                </li>
                <li><a href="{{ route('pages.return-policy') }}" class="hover:text-gray-900 transition">İade
                        Politikası</a></li>
                <li><a href="{{ route('pages.faq') }}" class="hover:text-gray-900 transition">SSS</a></li>
            </ul>
        </div>
        <div>
            <h4 class="text-[11px] uppercase  text-gray-800 font-semibold">Kategoriler</h4>
            <ul class="mt-4 space-y-2 text-[10px]">
                @foreach (($categories ?? collect())->whereNull('category_id')->take(10) as $category)
                    <li><a href="{{ route('categories.show', [$category->id, $category->slug]) }}"
                            class="hover:text-gray-900 transition">{{ $category->name }}</a></li>
                @endforeach
            </ul>
        </div>
        <div>
            <h4 class="text-[11px] uppercase  text-gray-800 font-semibold">Kurumsal</h4>
            <ul class="mt-4 space-y-2 text-[10px]">
                <li><a href="{{ route('pages.about') }}" class="hover:text-gray-900 transition">Hakkımızda</a></li>
                <li><a href="{{ route('pages.privacy') }}" class="hover:text-gray-900 transition">Gizlilik
                        Politikası</a></li>
                <li><a href="{{ route('pages.cookies') }}" class="hover:text-gray-900 transition">Çerez Politikası</a>
                </li>
                <li><a href="{{ route('pages.distance-selling') }}" class="hover:text-gray-900 transition">Mesafeli
                        Satış</a></li>
            </ul>
        </div>
    </div>
    <div>
        <div
            class="max-w-[1680px] mx-auto px-4 md:px-8 py-4 text-gray-500 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-5 text-xs">
                <a href="{{ $settings->instagram }}" class="hover:text-gray-900 transition"><i
                        class="ri-instagram-line"></i></a>
                <a href="{{ $settings->facebook }}" class="hover:text-gray-900 transition"><i
                        class="ri-facebook-line"></i></a>
                <a href="{{ $settings->twitter }}" class="hover:text-gray-900 transition"><i
                        class="ri-twitter-x-line"></i></a>
                <a href="{{ $settings->youtube }}" class="hover:text-gray-900 transition"><i
                        class="ri-youtube-line"></i></a>
                <a href="{{ $settings->linkedin }}" class="hover:text-gray-900 transition"><i
                        class="ri-linkedin-line"></i></a>
            </div>
            <span class="text-[11px] flex w-full justify-center md:justify-end items-center gap-2">&copy; 2026 <a
                    href="https://macroturk.com" target="_blank"><img src="{{ asset('assets/img/macroturklogo.svg') }}"
                        alt="Payment Methods" class="w-14 h-auto"></a> Tüm Hakları Saklıdır.</span>
        </div>
    </div>
</footer>