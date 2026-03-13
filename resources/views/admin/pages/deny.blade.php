<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Yönetim Girişi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.lineicons.com/4.0/lineicons.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>
    <style>
        @keyframes float {

            0%,
            100% {
                transform: translateY(0) translateX(0) rotate(0deg);
            }

            25% {
                transform: translateY(-20px) translateX(10px) rotate(5deg);
            }

            50% {
                transform: translateY(-40px) translateX(-10px) rotate(-5deg);
            }

            75% {
                transform: translateY(-20px) translateX(5px) rotate(3deg);
            }
        }

        .floating-icon {
            animation: float 12s infinite ease-in-out;
        }

        .floating-icon:nth-child(1) {
            animation-delay: 0s;
        }

        .floating-icon:nth-child(2) {
            animation-delay: 0.4s;
        }

        .floating-icon:nth-child(3) {
            animation-delay: 0.8s;
        }

        .floating-icon:nth-child(4) {
            animation-delay: 1.2s;
        }

        .floating-icon:nth-child(5) {
            animation-delay: 1.6s;
        }

        .floating-icon:nth-child(6) {
            animation-delay: 2s;
        }

        .floating-icon:nth-child(7) {
            animation-delay: 2.4s;
        }

        .floating-icon:nth-child(8) {
            animation-delay: 2.8s;
        }

        .floating-icon:nth-child(9) {
            animation-delay: 3.2s;
        }

        .floating-icon:nth-child(10) {
            animation-delay: 3.6s;
        }

        .floating-icon:nth-child(11) {
            animation-delay: 4s;
        }

        .floating-icon:nth-child(12) {
            animation-delay: 4.4s;
        }

        .floating-icon:nth-child(13) {
            animation-delay: 4.8s;
        }

        .floating-icon:nth-child(14) {
            animation-delay: 5.2s;
        }

        .floating-icon:nth-child(15) {
            animation-delay: 5.6s;
        }

        .floating-icon:nth-child(16) {
            animation-delay: 6s;
        }

        .floating-icon:nth-child(17) {
            animation-delay: 6.4s;
        }

        .floating-icon:nth-child(18) {
            animation-delay: 6.8s;
        }

        .floating-icon:nth-child(19) {
            animation-delay: 7.2s;
        }

        .floating-icon:nth-child(20) {
            animation-delay: 7.6s;
        }

        .floating-icon:nth-child(21) {
            animation-delay: 8s;
        }

        .floating-icon:nth-child(22) {
            animation-delay: 8.4s;
        }

        .floating-icon:nth-child(23) {
            animation-delay: 8.8s;
        }

        .floating-icon:nth-child(24) {
            animation-delay: 9.2s;
        }

        .floating-icon:nth-child(25) {
            animation-delay: 9.6s;
        }

        .floating-icon:nth-child(26) {
            animation-delay: 10s;
        }

        .floating-icon:nth-child(27) {
            animation-delay: 10.4s;
        }

        .floating-icon:nth-child(28) {
            animation-delay: 10.8s;
        }

        .floating-icon:nth-child(29) {
            animation-delay: 11.2s;
        }

        .floating-icon:nth-child(30) {
            animation-delay: 11.6s;
        }
    </style>
</head>

<body class="min-h-screen bg-white text-black dark:bg-black dark:text-white relative overflow-hidden">
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <i class="ri-store-3-line floating-icon absolute text-3xl opacity-10" style="top: 5%; left: 3%;"></i>
        <i class="ri-shopping-cart-2-line floating-icon absolute text-4xl opacity-10" style="top: 12%; right: 8%;"></i>
        <i class="ri-box-3-line floating-icon absolute text-3xl opacity-10" style="top: 18%; left: 12%;"></i>
        <i class="ri-truck-line floating-icon absolute text-4xl opacity-10" style="top: 25%; right: 4%;"></i>
        <i class="ri-wallet-3-line floating-icon absolute text-3xl opacity-10" style="top: 32%; left: 6%;"></i>
        <i class="ri-gift-line floating-icon absolute text-4xl opacity-10" style="top: 38%; right: 12%;"></i>
        <i class="ri-coupon-line floating-icon absolute text-3xl opacity-10" style="top: 45%; left: 10%;"></i>
        <i class="ri-bar-chart-line floating-icon absolute text-4xl opacity-10" style="top: 52%; right: 6%;"></i>
        <i class="ri-user-line floating-icon absolute text-3xl opacity-10" style="top: 8%; left: 20%;"></i>
        <i class="ri-settings-3-line floating-icon absolute text-4xl opacity-10" style="top: 58%; right: 18%;"></i>
        <i class="ri-store-3-line floating-icon absolute text-3xl opacity-10" style="top: 65%; left: 25%;"></i>
        <i class="ri-shopping-cart-2-line floating-icon absolute text-4xl opacity-10" style="top: 72%; right: 22%;"></i>
        <i class="ri-box-3-line floating-icon absolute text-3xl opacity-10" style="top: 15%; left: 35%;"></i>
        <i class="ri-truck-line floating-icon absolute text-4xl opacity-10" style="top: 22%; right: 28%;"></i>
        <i class="ri-wallet-3-line floating-icon absolute text-3xl opacity-10" style="top: 28%; left: 42%;"></i>
        <i class="ri-gift-line floating-icon absolute text-4xl opacity-10" style="top: 35%; right: 35%;"></i>
        <i class="ri-coupon-line floating-icon absolute text-3xl opacity-10" style="top: 42%; left: 48%;"></i>
        <i class="ri-bar-chart-line floating-icon absolute text-4xl opacity-10" style="top: 48%; right: 42%;"></i>
        <i class="ri-user-line floating-icon absolute text-3xl opacity-10" style="top: 55%; left: 55%;"></i>
        <i class="ri-settings-3-line floating-icon absolute text-4xl opacity-10" style="top: 62%; right: 48%;"></i>
        <i class="ri-store-3-line floating-icon absolute text-3xl opacity-10" style="top: 68%; left: 62%;"></i>
        <i class="ri-shopping-cart-2-line floating-icon absolute text-4xl opacity-10" style="top: 75%; right: 55%;"></i>
        <i class="ri-box-3-line floating-icon absolute text-3xl opacity-10" style="top: 82%; left: 68%;"></i>
        <i class="ri-truck-line floating-icon absolute text-4xl opacity-10" style="top: 88%; right: 62%;"></i>
        <i class="ri-wallet-3-line floating-icon absolute text-3xl opacity-10" style="top: 10%; left: 75%;"></i>
        <i class="ri-gift-line floating-icon absolute text-4xl opacity-10" style="top: 18%; right: 72%;"></i>
        <i class="ri-coupon-line floating-icon absolute text-3xl opacity-10" style="top: 25%; left: 82%;"></i>
        <i class="ri-bar-chart-line floating-icon absolute text-4xl opacity-10" style="top: 32%; right: 78%;"></i>
        <i class="ri-user-line floating-icon absolute text-3xl opacity-10" style="top: 40%; left: 88%;"></i>
        <i class="ri-settings-3-line floating-icon absolute text-4xl opacity-10" style="top: 48%; right: 85%;"></i>
    </div>

    <div class="absolute right-4 top-4 z-50">
        <button onclick="toggleTheme()"
            class="px-3 py-1.5 rounded-md border border-gray-200 dark:border-gray-800 hover:bg-gray-100 dark:hover:bg-gray-900 text-sm transition-colors">
            <i class="ri-moon-line"></i>
        </button>
    </div>

    <div class="min-h-screen flex items-center justify-center p-4 relative z-10">
        <div
            class="w-full max-w-md p-8 rounded-2xl border border-gray-200 dark:border-gray-800 bg-white/80 dark:bg-black/60 backdrop-blur-xl shadow-xl">
            <div class="text-center mb-8">
                @if ($settings && $settings->logo)
                    <div class="mb-4">
                        <img src="{{ $settings->logo }}" alt="Logo" class="mx-auto h-10 w-auto object-contain">
                    </div>
                @else
                    <div
                        class="mx-auto w-16 h-16 rounded-full border-2 border-gray-300 dark:border-gray-700 flex items-center justify-center mb-4">
                        <i class="ri-shield-line text-2xl text-gray-400"></i>
                    </div>
                @endif
            </div>
            @if ($errors->any())
                <div
                    class="mb-4 p-3 rounded-md bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-sm text-red-600 dark:text-red-400">
                    {{ $errors->first() }}
                </div>
            @endif
        </div>
    </div>

    @include('admin.parts.footer')

    <script>
        (function initTheme() {
            try {
                const saved = localStorage.getItem('admin-theme');
                if (saved === 'dark' || (!saved && window.matchMedia && window.matchMedia(
                        '(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            } catch (_) {}
        })();

        function toggleTheme() {
            const isDark = document.documentElement.classList.toggle('dark');
            try {
                localStorage.setItem('admin-theme', isDark ? 'dark' : 'light');
            } catch (_) {}
        }

        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('login-password');
            const toggleBtn = document.querySelector('[data-toggle-password]');
            if (!passwordInput || !toggleBtn) return;

            toggleBtn.addEventListener('click', function() {
                const isHidden = passwordInput.type === 'password';
                passwordInput.type = isHidden ? 'text' : 'password';
                const icon = toggleBtn.querySelector('i');
                if (!icon) return;
                icon.classList.toggle('ri-eye-off-line', !isHidden);
                icon.classList.toggle('ri-eye-line', isHidden);
            });
        });
    </script>
</body>

</html>
