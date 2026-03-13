@php

    $slides = ($sliders ?? collect())->filter(fn($slide) => $slide?->image)->values();

@endphp



@if ($slides->isNotEmpty())

    <section class="home-hero {{ $theme->color ? 'bg-' . $theme->color : 'bg-blue-600' }}">

        <div class="swiper js-home-hero {{ $theme->color ? 'text-' . $theme->color : '' }}">

            <div class="swiper-wrapper">

                @foreach ($slides as $slide)
                    <div class="swiper-slide">

                        <div class="home-hero__slide !min-h-[550px] md:!min-h-[75vh]">

                            <div class="home-hero__media">
                                <img src="{{ asset($slide->image) }}" alt="{{ $slide->title }}"
                                    class="home-hero__image hidden md:block w-full h-full">

                                <img src="{{ asset($slide->mobile_image ?: $slide->image) }}" alt="{{ $slide->title }}"
                                    class="home-hero__image block md:hidden w-full h-full">
                            </div>

                            <div class="container home-hero__content">

                                <div class="home-hero__body !max-w-xl text-left items-start space-y-6">

                                    @if ($slide->subtitle)
                                        <p
                                            class="home-hero__kicker !text-gray-900 !opacity-100 font-bold text-sm md:text-base">
                                            {{ $slide->subtitle }}
                                        </p>
                                    @endif

                                    <h1
                                        class="home-hero__title !text-3xl md:!text-6xl font-bold uppercase tracking-tighter text-gray-900">
                                        {{ $slide->title }}
                                    </h1>

                                    @if ($slide->description)
                                        <p
                                            class="home-hero__description !text-gray-600 max-w-lg text-sm md:text-lg font-medium leading-relaxed">
                                            {!! strip_tags($slide->description) !!}
                                        </p>
                                    @endif

                                    @if ($slide->button_link)
                                        <div class="home-hero__actions">
                                            <a href="{{ $slide->button_link }}"
                                                class="inline-block bg-black text-white px-6 py-3 rounded-full text-xs md:text-sm font-bold uppercase tracking-widest hover:bg-gray-800 transition-all shadow-xl">
                                                {{ $slide->button_text ?? 'Alışverişe Başla' }}
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
