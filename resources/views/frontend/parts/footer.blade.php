<footer
    class="bg-white border-t {{ $theme->color ? 'border-' . $theme->color . '/20' : 'border-gray-200' }} text-gray-600 mt-16">

    @if ($footer_campaign)
        @include('frontend.parts.campaings.footer')
    @endif

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12 grid grid-cols-2 md:grid-cols-4 gap-4">

        <div class="md:block hidden">

            <a href="{{ route('home') }}" class="inline-flex items-center gap-3">

                @if (!empty($settings?->logo))
                    <img src="{{ $settings->logo }}" alt="{{ $settings->title ?? config('app.name') }}"
                        class="h-12 w-auto object-contain">
                @else
                    <span
                        class="text-xl font-semibold text-text-gray-600">{{ $settings->title ?? config('app.name') }}</span>
                @endif

            </a>

            <p class="mt-4 text-xs text-text-gray-600 leading-relaxed">

                {!! strip_tags($store->meta_description) !!}

            </p>

        </div>

        <div>

            <h4
                class="text-sm font-semibold {{ $theme->color ? 'text-' . $theme->color : 'text-gray-600' }} uppercase tracking-wide">

                Kategoriler</h4>

            <ul class="mt-4 space-y-2 text-sm">

                @foreach (($categories ?? collect())->whereNull('category_id')->take(10) as $category)
                    <li>
                        <i class="ri-arrow-right-s-fill"></i>
                        <a href="{{ route('categories.show', [$category->id, $category->slug]) }}"
                            class="hover:{{ $theme->color ? 'text-' . $theme->color : 'text-gray-600' }} transition">{{ $category->name }}</a>

                    </li>
                @endforeach

            </ul>

        </div>


        <div>
            <h4
                class="text-sm font-semibold {{ $theme->color ? 'text-' . $theme->color : 'text-gray-600' }} uppercase tracking-wide">

                Site Haritası</h4>

            <ul class="mt-4 space-y-2 text-sm">
                <li class="flex items-center gap-2 text-sm">
                    <i class="ri-arrow-right-s-fill"></i>
                    <a href="{{ route('pages.about') }}"
                        class="hover:{{ $theme->color ? 'text-' . $theme->color : 'text-gray-600' }} transition">Hakkımızda</a>
                </li>
                <li class="flex items-center gap-2 text-sm">
                    <i class="ri-arrow-right-s-fill"></i>
                    <a href="{{ route('pages.privacy') }}"
                        class="hover:{{ $theme->color ? 'text-' . $theme->color : 'text-gray-600' }} transition">Gizlilik
                        Politikası</a>
                </li>
                <li class="flex items-center gap-2 text-sm">
                    <i class="ri-arrow-right-s-fill"></i>
                    <a href="{{ route('pages.cookies') }}"
                        class="hover:{{ $theme->color ? 'text-' . $theme->color : 'text-gray-600' }} transition">Çerez
                        Politikası</a>
                </li>
                <li class="flex items-center gap-2 text-sm">
                    <i class="ri-arrow-right-s-fill"></i>
                    <a href="{{ route('pages.distance-selling') }}"
                        class="hover:{{ $theme->color ? 'text-' . $theme->color : 'text-gray-600' }} transition">
                        Mesafeli Satış Sözleşmesi
                    </a>
                </li>
            </ul>
        </div>
        <div class="col-span-2 md:col-span-1">

            <h4
                class="text-sm font-semibold {{ $theme->color ? 'text-' . $theme->color : 'text-gray-600' }} uppercase tracking-wide">

                İletişim</h4>

            <ul class="mt-4 space-y-2 text-sm">

                @if (!empty($settings?->email))
                    <li class="flex items-center gap-2">

                        <i class="ri-mail-line"></i>

                        <a href="mailto:{{ $settings->email }}"
                            class="hover:{{ $theme->color ? 'text-' . $theme->color : 'text-gray-600' }} transition">

                            {{ $settings->email }}

                        </a>

                    </li>
                @endif

                @if (!empty($settings?->phone))
                    <li class="flex items-center gap-2">

                        <i class="ri-phone-line"></i>

                        <a href="tel:{{ $settings->phone }}"
                            class="hover:{{ $theme->color ? 'text-' . $theme->color : 'text-gray-600' }} transition">

                            {{ $settings->phone }}

                        </a>

                    </li>
                @endif

                @if (!empty($settings?->address))
                    <li class="flex items-start gap-2">

                        <i class="ri-map-pin-line mt-1"></i>

                        <span>{{ $settings->address }}</span>

                    </li>
                @endif

            </ul>

        </div>
    </div>

    <div class="bg-colormain border-t {{ $theme->color ? 'border-' . $theme->color . '/30' : 'border-gray-200' }}">

        <div
            class="container mx-auto px-4 lg:px-8 py-4 text-gray-500 flex flex-col md:flex-row items-center justify-between gap-4">

            <span class="text-xs">&copy; {{ date('Y') }} {{ env('ORIGINATOR_NAME') }}. Tüm hakları

                saklıdır.</span>

            <div class="flex items-center gap-6">

                <a href="{{ $settings->facebook }}"
                    class="hover:{{ $theme->color ? 'text-' . $theme->color : 'text-gray-600' }} transition md:text-md"><i
                        class="ri-facebook-fill"></i></a>
                <a href="{{ $settings->twitter }}"
                    class="hover:{{ $theme->color ? 'text-' . $theme->color : 'text-gray-600' }} transition md:text-md"><i
                        class="ri-twitter-fill"></i></a>
                <a href="{{ $settings->instagram }}"
                    class="hover:{{ $theme->color ? 'text-' . $theme->color : 'text-gray-600' }} transition md:text-md"><i
                        class="ri-instagram-fill"></i></a>
                <a href="{{ $settings->linkedin }}"
                    class="hover:{{ $theme->color ? 'text-' . $theme->color : 'text-gray-600' }} transition md:text-md"><i
                        class="ri-linkedin-fill"></i></a>
                <a href="{{ $settings->youtube }}"
                    class="hover:{{ $theme->color ? 'text-' . $theme->color : 'text-gray-600' }} transition md:text-md"><i
                        class="ri-youtube-fill"></i></a>
            </div>
        </div>
    </div>
</footer>
