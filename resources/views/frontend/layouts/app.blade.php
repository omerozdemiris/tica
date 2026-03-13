<!DOCTYPE html>
<html lang="tr" class="h-full">

<head>
    @if ($store->facebook_meta_code ?? null)
        <script>
            ! function(f, b, e, v, n, t, s) {
                if (f.fbq) return;
                n = f.fbq = function() {
                    n.callMethod ?
                        n.callMethod.apply(n, arguments) : n.queue.push(arguments)
                };
                if (!f._fbq) f._fbq = n;
                n.push = n;
                n.loaded = !0;
                n.version = '2.0';
                n.queue = [];
                t = b.createElement(e);
                t.async = !0;
                t.src = v;
                s = b.getElementsByTagName(e)[0];
                s.parentNode.insertBefore(t, s)
            }
            (window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '{{ $store->facebook_meta_code }}');
            fbq('track', 'PageView');
        </script>
        <noscript>
            <img height="1" width="1" style="display:none"
                src="https://www.facebook.com/tr?id={{ $store->facebook_meta_code }}&ev=PageView&noscript=1" />
        </noscript>
    @endif
    @if ($store->google_tag_manager ?? null)
        <script>
            (function(w, d, s, l, i) {
                w[l] = w[l] || [];
                w[l].push({
                    'gtm.start': new Date().getTime(),
                    event: 'gtm.js'
                });
                var f = d.getElementsByTagName(s)[0],
                    j = d.createElement(s),
                    dl = l != 'dataLayer' ? '&l=' + l : '';
                j.async = true;
                j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
                f.parentNode.insertBefore(j, f);
            })(window, document, 'script', 'dataLayer', '{{ $store->google_tag_manager }}');
        </script>
    @endif
    @if ($store->google_ads ?? null)
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $store->google_ads }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];

            function gtag() {
                dataLayer.push(arguments);
            }
            gtag('js', new Date());
            gtag('config', '{{ $store->google_ads }}');
        </script>
    @endif
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') | {{ $settings->title ?? env('APP_NAME') }}</title>
    <meta name="description" content="{!! strip_tags($store->meta_description ?? '') !!}">
    @if (request()->path() == '/')
        <meta property="og:title" content="{!! strip_tags($store->meta_title ?? '') !!}">
        <meta property="og:description" content="{!! strip_tags($store->meta_description ?? '') !!}">
    @else
        <meta property="og:title" content="@yield('og_title') | {{ $settings->title ?? env('APP_NAME') }}">
        <meta property="og:description" content="@yield('og_description')">
    @endif
    <meta property="og:image" content="{{ $settings->logo ?? '' }}">
    <meta property="og:type" content="website">
    <link rel="icon" type="image/png" href="{{ $settings->favicon ?? asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css">
    <link rel="stylesheet" href="{{ asset('assets/campaign.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend.css') }}">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        colormain: '#f7f7f7',
                    }
                },
            },
        };
    </script>

    <style>
        * {
            font-family: 'Outfit', sans-serif;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>

</head>



<body class="min-h-screen bg-colormain text-gray-900 flex flex-col">

    @include('frontend.parts.header')

    @if ($isMobile)
        @include('frontend.parts.mobile.menu')
    @endif

    @if (!$isMobile)

        @if ($header_campaign)
            @include('frontend.parts.campaings.header')
        @endif

    @endif

    <main class="flex-1">
        @yield('content')
    </main>
    @include('frontend.parts.footer')
    @include('frontend.parts.mobile.mobile_nav')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
    <script src="/assets/alert.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/not-a-toast@latest/dist/not-a-toast.min.js"></script>
    <script src="{{ asset('assets/alert.js') }}" defer></script>
    <script src="{{ asset('assets/campaign.js') }}" defer></script>
    <script src="{{ asset('assets/app.js') }}"></script>
    <script src="{{ asset('assets/frontend.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/vanilla-lazyload@17.8.3/dist/lazyload.min.js"></script>
    @stack('scripts')
</body>

</html>
