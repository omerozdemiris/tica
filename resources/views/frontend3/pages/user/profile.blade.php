@extends($template . '.layouts.app')
@section('title', 'Profil Bilgileri')
@section('breadcrumb_title', 'Profil Bilgileri')
@section('content')
    @php
        $user = $data->user ?? auth()->user();
    @endphp

    @include($template . '.parts.breadcrumb')
    <div class="bg-white border-b border-gray-100">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <p class="text-sm text-gray-500">Kişisel bilgilerinizi ve şifre bilgilerinizi buradan güncelleyebilirsiniz.</p>
        </div>
    </div>

    <section class="py-10">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white border border-gray-200 rounded-3xl shadow-sm p-8">
                @if (session('status'))
                    <div class="mb-6 p-4 rounded-2xl bg-green-50 border border-green-100 text-green-700 text-sm">
                        {{ session('status') }}
                    </div>
                @endif

                <form action="{{ route('user.profile.update') }}" method="POST" class="space-y-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label
                                class="block text-sm font-medium {{ $theme->color ? 'text-' . $theme->color : 'text-gray-700' }}">Ad
                                Soyad</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}"
                                class="mt-2 w-full px-4 py-3 border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} rounded-xl focus:border-{{ $theme->color ? 'border-' . $theme->color : 'blue-500' }} focus:ring-0">
                            @error('name')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium {{ $theme->color ? 'text-' . $theme->color : 'text-gray-700' }}">E-posta</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}"
                                class="mt-2 w-full px-4 py-3 border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} rounded-xl focus:border-{{ $theme->color ? 'border-' . $theme->color : 'blue-500' }} focus:ring-0">
                            @error('email')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium {{ $theme->color ? 'text-' . $theme->color : 'text-gray-700' }}">Telefon</label>
                            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                                class="mt-2 w-full px-4 py-3 border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} rounded-xl focus:border-{{ $theme->color ? 'border-' . $theme->color : 'blue-500' }} focus:ring-0">
                            @error('phone')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label
                                class="block text-sm font-medium {{ $theme->color ? 'text-' . $theme->color : 'text-gray-700' }}">Yeni
                                Şifre</label>
                            <input type="password" name="password"
                                class="mt-2 w-full px-4 py-3 border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} rounded-xl focus:border-{{ $theme->color ? 'border-' . $theme->color : 'blue-500' }} focus:ring-0">
                            @error('password')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium {{ $theme->color ? 'text-' . $theme->color : 'text-gray-700' }}">Yeni
                                Şifre (Tekrar)</label>
                            <input type="password" name="password_confirmation"
                                class="mt-2 w-full px-4 py-3 border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} rounded-xl focus:border-{{ $theme->color ? 'border-' . $theme->color : 'blue-500' }} focus:ring-0">
                        </div>
                    </div>
                    <div class="space-y-8 pt-6 border-t border-gray-100">
                        <!-- Web Bildirimleri -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 border-b border-gray-100 pb-2">
                                <i
                                    class="ri-global-line text-xl {{ $theme->color ? 'text-' . $theme->color : 'text-blue-600' }}"></i>
                                <span
                                    class="text-md font-semibold {{ $theme->color ? 'text-' . $theme->color : 'text-gray-700' }}">Web
                                    Site Bildirimleri</span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <label class="flex flex-col cursor-pointer group">
                                    <div class="flex items-center justify-between gap-4">
                                        <span
                                            class="text-sm font-medium {{ $theme->color ? 'text-' . $theme->color : 'text-gray-700' }}">Fiyat
                                            İndirim Bildirimi</span>
                                        <div class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="notification_price_web" value="1"
                                                @checked($user->hasNotificationPermission('web', 'price')) class="sr-only peer">
                                            <div
                                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all {{ $theme->color ? 'peer-checked:bg-' . $theme->color : 'peer-checked:bg-blue-600' }}">
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-2">Tarayıcı üzerinden fiyat düşüş haberlerini
                                        alırsınız.</p>
                                </label>

                                <label class="flex flex-col cursor-pointer group">
                                    <div class="flex items-center justify-between gap-4">
                                        <span
                                            class="text-sm font-medium {{ $theme->color ? 'text-' . $theme->color : 'text-gray-700' }}">Stok
                                            Bildirimi</span>
                                        <div class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="notification_stock_web" value="1"
                                                @checked($user->hasNotificationPermission('web', 'stock')) class="sr-only peer">
                                            <div
                                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all {{ $theme->color ? 'peer-checked:bg-' . $theme->color : 'peer-checked:bg-blue-600' }}">
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-2">Tarayıcı üzerinden stok yenileme haberlerini
                                        alırsınız.</p>
                                </label>
                            </div>
                        </div>

                        <!-- E-Posta Bildirimleri -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 border-b border-gray-100 pb-2">
                                <i
                                    class="ri-mail-line text-xl {{ $theme->color ? 'text-' . $theme->color : 'text-blue-600' }}"></i>
                                <span
                                    class="text-md font-semibold {{ $theme->color ? 'text-' . $theme->color : 'text-gray-700' }}">E-Posta
                                    Bildirimleri</span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <label class="flex flex-col cursor-pointer group">
                                    <div class="flex items-center justify-between gap-4">
                                        <span
                                            class="text-sm font-medium {{ $theme->color ? 'text-' . $theme->color : 'text-gray-700' }}">Fiyat
                                            İndirim Bildirimi</span>
                                        <div class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="notification_price_email" value="1"
                                                @checked($user->hasNotificationPermission('email', 'price')) class="sr-only peer">
                                            <div
                                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all {{ $theme->color ? 'peer-checked:bg-' . $theme->color : 'peer-checked:bg-blue-600' }}">
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-2">E-posta adresinize fiyat düşüş haberleri
                                        gönderilir.</p>
                                </label>

                                <label class="flex flex-col cursor-pointer group">
                                    <div class="flex items-center justify-between gap-4">
                                        <span
                                            class="text-sm font-medium {{ $theme->color ? 'text-' . $theme->color : 'text-gray-700' }}">Stok
                                            Bildirimi</span>
                                        <div class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="notification_stock_email" value="1"
                                                @checked($user->hasNotificationPermission('email', 'stock')) class="sr-only peer">
                                            <div
                                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all {{ $theme->color ? 'peer-checked:bg-' . $theme->color : 'peer-checked:bg-blue-600' }}">
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-2">E-posta adresinize stok yenileme haberleri
                                        gönderilir.</p>
                                </label>
                            </div>
                        </div>

                        <!-- SMS Bildirimleri -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 border-b border-gray-100 pb-2">
                                <i
                                    class="ri-message-2-line text-xl {{ $theme->color ? 'text-' . $theme->color : 'text-blue-600' }}"></i>
                                <span
                                    class="text-md font-semibold {{ $theme->color ? 'text-' . $theme->color : 'text-gray-700' }}">SMS
                                    Bildirimleri</span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <label class="flex flex-col cursor-pointer group">
                                    <div class="flex items-center justify-between gap-4">
                                        <span
                                            class="text-sm font-medium {{ $theme->color ? 'text-' . $theme->color : 'text-gray-700' }}">Fiyat
                                            İndirim Bildirimi</span>
                                        <div class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="notification_price_sms" value="1"
                                                @checked($user->hasNotificationPermission('sms', 'price')) class="sr-only peer">
                                            <div
                                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all {{ $theme->color ? 'peer-checked:bg-' . $theme->color : 'peer-checked:bg-blue-600' }}">
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-2">Cep telefonunuza fiyat düşüş haberleri SMS olarak
                                        gönderilir.</p>
                                </label>

                                <label class="flex flex-col cursor-pointer group">
                                    <div class="flex items-center justify-between gap-4">
                                        <span
                                            class="text-sm font-medium {{ $theme->color ? 'text-' . $theme->color : 'text-gray-700' }}">Stok
                                            Bildirimi</span>
                                        <div class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="notification_stock_sms" value="1"
                                                @checked($user->hasNotificationPermission('sms', 'stock')) class="sr-only peer">
                                            <div
                                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all {{ $theme->color ? 'peer-checked:bg-' . $theme->color : 'peer-checked:bg-blue-600' }}">
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-2">Cep telefonunuza stok yenileme haberleri SMS
                                        olarak gönderilir.</p>
                                </label>
                            </div>
                        </div>
                    </div>

                    <button type="submit"
                        class="px-6 py-3 rounded-full {{ $theme->color ? 'bg-' . $theme->color : 'bg-blue-600' }} text-white text-sm font-semibold hover:{{ $theme->color ? 'text-' . $theme->color : 'text-white' }} hover:{{ $theme->color ? 'bg-' . $theme->color . '/20' : 'bg-blue-700' }} transition-colors">
                        Güncelle
                    </button>
                </form>
            </div>
        </div>
    </section>
@endsection
