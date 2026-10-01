@php
    $slides = ($sliders ?? collect())->filter(fn($slide) => $slide?->image)->values();
@endphp
@if ($slides->isNotEmpty())
    <section class="home-hero {{ $theme->color ? 'bg-' . $theme->color : 'bg-blue-600' }}">
        <div class="swiper js-home-hero {{ $theme->color ? 'text-' . $theme->color : '' }}">
            <div class="swiper-wrapper">
                @foreach ($slides as $slide)
                    <div class="swiper-slide">
                        <div class="home-hero__slide">
                            <div class="home-hero__media">
                                <img src="{{ asset($slide->image) }}" alt="{{ $slide->title }}"
                                    class="home-hero__image hidden md:block w-full h-full object-cover">
                                <img src="{{ asset($slide->mobile_image ?: $slide->image) }}" alt="{{ $slide->title }}"
                                    class="home-hero__image block md:hidden w-full h-full object-cover">
                            </div>
                            <div class="container home-hero__content">
                                <div class="home-hero__body">
                                    @if ($slide->subtitle)
                                        <p class="home-hero__kicker">{{ $slide->subtitle }}</p>
                                    @endif
                                    <h1 class="home-hero__title text-black">
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
                                            <a href="{{ $buttonUrl }}"
                                                class="bg-white text-black rounded-full px-6 py-4 text-sm md:text-md">
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
        </div>
        {{-- <div class="w-full mx-auto">
            <div class="relative w-full aspect-video overflow-hidden shadow-lg bg-gray-100">
                <video id="homepage-video" autoplay loop muted playsinline class="w-full h-full object-cover">
                    <source src="{{ asset('assets/img/video.mp4') }}" type="video/mp4">
                    Tarayıcınız video etiketini desteklemiyor.
                </video>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const video = document.getElementById('homepage-video');
                if (video) {
                    video.muted = true;
                    video.play().catch(function(error) {
                        console.log(
                            "Autoplay was prevented by browser policy. Video will play upon user interaction.",
                            error);
                        const playOnInteraction = () => {
                            video.play().catch(e => console.log(e));
                            document.removeEventListener('click', playOnInteraction);
                            document.removeEventListener('touchstart', playOnInteraction);
                        };
                        document.addEventListener('click', playOnInteraction);
                        document.addEventListener('touchstart', playOnInteraction);
                    });
                }
            });
        </script> --}}
    </section>
@endif
