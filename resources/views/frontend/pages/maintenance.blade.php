<!DOCTYPE html>
@php
    $meta = $meta ?? ($data->meta ?? (object) []);
    $metaTitle = $meta->title ?? ($settings->meta_title ?? config('app.name'));
    $metaDescription = $meta->description ?? ($settings->meta_description ?? '');
    $metaImage = $meta->image ?? ($settings->logo ?? asset('favicon.ico'));
@endphp
<html lang="tr" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $metaTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:image" content="{{ $metaImage }}">
    <meta property="og:type" content="website">
    <link rel="icon" type="image/png" href="{{ $settings->favicon ?? asset('favicon.ico') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>


<body class="min-h-screen bg-slate-900/80 text-white relative overflow-hidden">
    <div id="emoji-field"></div>
    <main class="min-h-screen flex flex-col items-center justify-center px-6 relative z-10">
        <div class="text-center max-w-7xl">
            <p class="text-sm uppercase tracking-[0.5em] text-slate-400 mb-4">Bakım Modu</p>
            <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">
                <span id="typewriter" class="inline-block border-r-4 border-white pr-2"></span>
            </h1>
            <p class="text-lg text-slate-300 leading-relaxed">
                Şu anda sizlere daha iyi bir deneyim sunmak için planlı bir bakım çalışması yapıyoruz.
                Kısa süre içinde tekrar hizmetinizde olacağız. Anlayışınız için teşekkür ederiz.
            </p>
            <div class="mt-10 flex flex-col items-center gap-3 text-sm text-slate-200">
                <div class="flex flex-wrap justify-center gap-3">
                    @if (!empty($settings->support_email))
                        <a href="mailto:{{ $settings->support_email }}"
                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 hover:bg-white/20 transition">
                            <i class="ri-mail-line"></i> {{ $settings->support_email }}
                        </a>
                    @endif
                    @if (!empty($settings->phone))
                        <a href="tel:{{ $settings->phone }}"
                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 hover:bg-white/20 transition">
                            <i class="ri-phone-line"></i> {{ $settings->phone }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </main>

    @if (!empty($settings->social_links))
        <footer class="absolute bottom-0 left-0 w-full py-6 bg-black/40 backdrop-blur-sm border-t border-white/10">
            <div class="container mx-auto px-6 flex flex-wrap items-center justify-center gap-4 text-slate-200 text-sm">
                <span class="text-xs uppercase tracking-widest text-slate-400">Bize ulaşın</span>
                @foreach ($settings->social_links as $label => $url)
                    <a href="{{ $url }}" target="_blank"
                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 hover:bg-white/20 text-xs uppercase tracking-wide">
                        <i class="ri-external-link-line text-base"></i> {{ ucfirst($label) }}
                    </a>
                @endforeach
            </div>
        </footer>
    @endif

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const emojis = [];
            const field = document.getElementById("emoji-field");
            const count = 60;

            for (let i = 0; i < count; i++) {
                const span = document.createElement("span");
                span.textContent = emojis[Math.floor(Math.random() * emojis.length)];
                span.className = "emoji-particle";
                span.style.left = Math.random() * 100 + "%";
                span.style.animationDuration = 20 + Math.random() * 20 + "s";
                span.style.fontSize = Math.random() < 0.5 ? "12px" : "16px";
                span.style.animationDelay = -Math.random() * 20 + "s";
                field.appendChild(span);
            }

            const phrases = [
                "Yakında Yeniden Buradayız",
                "Daha Kolay Bir Deneyim İçin Çalışıyoruz"
            ];

            let currentIndex = 0;
            const typewriter = document.getElementById("typewriter");
            let isDeleting = false;
            let text = "";

            function type() {
                const currentPhrase = phrases[currentIndex];

                if (!isDeleting) {
                    text = currentPhrase.substring(0, text.length + 1);
                    typewriter.textContent = text;
                    if (text === currentPhrase) {
                        setTimeout(() => (isDeleting = true), 3000);
                    }
                } else {
                    text = currentPhrase.substring(0, text.length - 1);
                    typewriter.textContent = text;
                    if (text === "") {
                        isDeleting = false;
                        currentIndex = (currentIndex + 1) % phrases.length;
                    }
                }

                const speed = isDeleting ? 20 : 100;
                setTimeout(type, speed);
            }

            type();
        });
    </script>

    <style>
        #emoji-field {
            position: absolute;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .emoji-particle {
            position: absolute;
            top: -5%;
            color: rgba(255, 255, 255, 0.2);
            animation: float linear infinite;
        }

        @keyframes float {
            0% {
                transform: translateY(0) translateX(0);
                opacity: 0;
            }

            10% {
                opacity: 0.6;
            }

            80% {
                opacity: 0.3;
            }

            100% {
                transform: translateY(120vh) translateX(calc(-50px + 100px * var(--random-x)));
                opacity: 0;
            }
        }
    </style>
</body>

</html>
