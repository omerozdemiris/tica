@php
    $adminUser = null;
    if (session('admin_user_id')) {
        $adminUser = \App\Models\User::find(session('admin_user_id'));
    }
    $initials = $adminUser ? mb_strtoupper(mb_substr($adminUser->name ?: $adminUser->username ?: 'A', 0, 1)) : 'A';
    $logoLight = $settings->logo ?? asset('admin/assets/logo-light.svg');
    $logoDark = $settings->white_logo ?? ($settings->logo ?? asset('admin/assets/logo-dark.svg'));
@endphp
<header
    class="w-full z-10 shadow-sm border-b fixed border-gray-200 dark:border-gray-800 bg-white/60 dark:bg-black/40 backdrop-blur">
    <div class="container px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-2 md:hidden">
            <button type="button" @click="mobileSidebarOpen = true" class="p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-900">
                <i class="ri-menu-line"></i>
            </button>
        </div>
        <div class="flex-1">
            <a href="{{ route('admin.dashboard') }}" class="relative h-9 w-auto inline-block">
                <img src="{{ $logoLight }}" alt="{{ config('app.name') }} logo"
                    class="h-9 p-2 w-auto object-contain transition-opacity duration-300 ease-out" id="light-logo">
                <img src="{{ $logoDark }}" alt="{{ config('app.name') }} koyu logo"
                    class="h-9 p-2 w-auto object-contain transition-opacity duration-300 ease-out absolute inset-0 opacity-0 pointer-events-none"
                    id="dark-logo">
            </a>
        </div>
        <div class="relative flex items-center gap-6">
            <div
                class="hidden md:flex items-center gap-2 px-3 py-1.5 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 text-white shadow-lg">
                <i class="ri-group-line text-sm"></i>
                <span class="text-xs">Toplam Ziyaretçi : </span><span class="text-sm font-semibold"
                    id="header-visitor-count">{{ $settings->visitor_count }}</span>
            </div>
            <a href="{{ config('app.url') }}" target="_blank" rel="noopener"
                class="hidden md:inline-flex px-3 py-1.5 rounded-md border border-gray-200 dark:border-gray-800 hover:bg-gray-100 dark:hover:bg-gray-900 text-sm">Mağazayı
                Aç</a>
            <button type="button" onclick="toggleTheme()"
                class="px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 hover:bg-gray-100 dark:hover:bg-gray-900 text-sm">
                <i class="ri-moon-line"></i>
            </button>
            <button type="button" id="admin-avatar"
                class="w-8 h-8 rounded-full bg-black text-white dark:bg-white dark:text-black flex items-center justify-center text-xs font-semibold">
                {{ $initials }}
            </button>
            <div id="admin-menu"
                class="hidden absolute right-0 top-10 w-44 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black shadow">
                <a href="{{ route('admin.account.index') }}"
                    class="block px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-900 text-sm">Hesap Ayarları</a>
                <a href="{{ config('app.url') }}" target="_blank" rel="noopener"
                    class="block px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-900 text-sm">Mağazayı Aç</a>
                <form class="mb-0" method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button
                        class="w-full text-left block px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-900 text-sm">Çıkış</button>
                </form>
            </div>
        </div>
    </div>
    @push('scripts')
        <script>
            document.getElementById('admin-avatar')?.addEventListener('click', function() {
                const m = document.getElementById('admin-menu');
                if (m) m.classList.toggle('hidden');
            });
            document.addEventListener('click', function(e) {
                const menu = document.getElementById('admin-menu');
                const btn = document.getElementById('admin-avatar');
                if (menu && !menu.contains(e.target) && !btn.contains(e.target)) menu.classList.add('hidden');
            });
        </script>
    @endpush
</header>
