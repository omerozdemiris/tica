@extends('admin.layouts.app')

@section('title', 'Kullanıcı Oluştur')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
            <div
                class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-500 to-indigo-600 text-white flex items-center justify-center text-sm font-semibold shadow">
                <span id="user-avatar-initials">NA</span>
            </div>
            <div>
                <h1 class="text-lg font-semibold flex items-center gap-2">
                    Yeni Kullanıcı
                    <span
                        class="text-[10px] uppercase tracking-wide px-2 py-0.5 rounded-full border border-gray-200 dark:border-gray-700 bg-blue-50 text-blue-700 dark:bg-blue-900 dark:text-blue-100">
                        Admin
                    </span>
                </h1>
                <p class="text-sm text-gray-500">Yeni bir yönetici hesabı oluşturun ve gerekli modül izinlerini atayın.</p>
            </div>
        </div>
        <a href="{{ route('admin.users.index') }}"
            class="px-3 py-2 rounded-md text-sm border border-gray-200 dark:border-gray-800 inline-flex items-center gap-2 hover:bg-gray-50 dark:hover:bg-gray-900 transition">
            <i class="ri-arrow-left-line"></i>
            <span>Listeye dön</span>
        </a>
    </div>

    @if ($errors->any())
        <div class="mb-4 px-4 py-3 rounded border border-red-200 bg-red-50 text-sm text-red-800">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-6">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium">Ad Soyad</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                    class="mt-1 w-full px-3 py-2 rounded border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
            </div>
            <div>
                <label class="text-sm font-medium">E-posta</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                    class="mt-1 w-full px-3 py-2 rounded border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
            </div>
            <div>
                <label class="text-sm font-medium">Kullanıcı adı</label>
                <input type="text" name="username" value="{{ old('username') }}" required
                    class="mt-1 w-full px-3 py-2 rounded border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
            </div>
            <div>
                <label class="text-sm font-medium">Şifre</label>
                <input type="password" name="password" required
                    class="mt-1 w-full px-3 py-2 rounded border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
            </div>
            {{-- <div>
                <label class="text-sm font-medium">Rol</label>
                <select name="role" id="role-select"
                    class="mt-1 w-full px-3 py-2 rounded border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                    <option value="1" {{ old('role') == 1 ? 'selected' : '' }}>Admin</option>
                    <option value="2" {{ old('role') == 2 ? 'selected' : '' }}>Süper Admin</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">Süper admin tüm izinlere sahiptir.</p>
            </div> --}}
        </div>

        <div id="permissions-panel" class="rounded-lg border border-gray-200 dark:border-gray-800 p-4 mt-4">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h2 class="text-sm font-semibold">İzinler</h2>
                    <p class="text-xs text-gray-500">Admin için erişilecek modülleri seç</p>
                </div>
                <button type="button" id="toggle-all"
                    class="text-xs px-3 py-1 rounded border border-gray-200 dark:border-gray-800">Tümünü seç</button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                @foreach ($routes as $route)
                    @continue(in_array($route->name, config('admin.excluded_admin_routes', [])))
                    <label
                        class="flex items-center justify-between gap-3 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900 transition">
                        <div class="flex items-center gap-2 text-sm">
                            <i class="ri-shield-keyhole-line text-gray-400"></i>
                            <span class="capitalize">{{ $route->name }}</span>
                        </div>
                        <input type="checkbox" name="routes[]" value="{{ $route->id }}" class="toggle">
                    </label>
                @endforeach
            </div>
        </div>

        <div class="flex items-center justify-end gap-2">
            <button type="submit"
                class="px-4 py-2 rounded-md bg-black text-white dark:bg-white dark:text-black text-sm">Kaydet</button>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const panel = document.getElementById('permissions-panel');
            const toggleAll = document.getElementById('toggle-all');
            const nameInput = document.querySelector('input[name="name"]');
            const avatarInitials = document.getElementById('user-avatar-initials');

            if (toggleAll && panel) {
                toggleAll.addEventListener('click', function() {
                    const inputs = panel.querySelectorAll('input[type="checkbox"]');
                    const shouldCheck = Array.from(inputs).some(i => !i.checked);
                    inputs.forEach(i => i.checked = shouldCheck);
                });
            }

            if (nameInput && avatarInitials) {
                const updateInitials = () => {
                    const value = nameInput.value.trim();
                    if (!value) {
                        avatarInitials.textContent = 'NA';
                        return;
                    }
                    const parts = value.split(/\s+/).filter(Boolean);
                    let letters = '';
                    if (parts[0]) letters += parts[0][0].toUpperCase();
                    if (parts.length > 1 && parts[parts.length - 1]) {
                        letters += parts[parts.length - 1][0].toUpperCase();
                    }
                    avatarInitials.textContent = letters || 'NA';
                };
                updateInitials();
                nameInput.addEventListener('input', updateInitials);
            }
        });
    </script>
@endpush
