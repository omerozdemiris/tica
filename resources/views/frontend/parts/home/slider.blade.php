@php

$slides = ($sliders ?? collect())->filter(fn($slide) => $slide?->image)->values();

@endphp



@if ($slides->isNotEmpty())

<section class="home-hero {{ $theme->color ? 'bg-'.$theme->color : 'bg-blue-600' }}">

    <div class="swiper js-home-hero {{ $theme->color ? 'text-'.$theme->color : '' }}">

        <div class="swiper-wrapper">

            @foreach ($slides as $slide)

            <div class="swiper-slide">

                <div class="home-hero__slide">

                    <div class="home-hero__media">
                        <img src="{{ asset($slide->image) }}"
                            alt="{{ $slide->title }}"
                            class="home-hero__image hidden md:block w-full h-full object-cover">

                        <img src="{{ asset($slide->mobile_image ?: $slide->image) }}"
                            alt="{{ $slide->title }}"
                            class="home-hero__image block md:hidden w-full h-full object-cover">

                        <div class="home-hero__overlay {{ $theme->color ? 'bg-'.$theme->color . '/30' : 'bg-gray-600/30' }}"></div>
                    </div>

                    <div class="container home-hero__content">

                        <div class="home-hero__body">

                            @if ($slide->subtitle)

                            <p class="home-hero__kicker">{{ $slide->subtitle }}</p>

                            @endif

                            <h1 class="home-hero__title">

                                {{ $slide->title }}

                            </h1>

                            @if ($slide->description)

                            <p class="home-hero__description">{!! strip_tags($slide->description) !!}</p>

                            @endif

                            @php

                            $buttonUrl = $slide->button_link ?: route('products.index');

                            @endphp

                            @if ($slide->button_link)

                            <div class="home-hero__actions">

                                <a href="{{ $buttonUrl }}" class="bg-white text-black rounded-full px-6 py-4 text-sm md:text-md">

                                    {{ $slide->button_text ?? 'Ürünleri İncele' }}

                                </a>

                            </div>

                            @endif

                        </div>

                    </div>

                </div>

            </div>

            @endforeach

        </div>

        <div class="home-hero__nav home-hero__nav--prev">

            <i class="ri-arrow-left-s-line"></i>

        </div>

        <div class="home-hero__nav home-hero__nav--next">

            <i class="ri-arrow-right-s-line"></i>

        </div>

    </div>

</section>

@endif