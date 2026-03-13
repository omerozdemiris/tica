@extends($template . '.layouts.app')
@section('title', 'Kayıt Ol')
@section('breadcrumb_title', 'Kayıt Ol')
@section('content')
    <div
        class="min-h-[70vh] flex items-center justify-center px-4 py-16 bg-gradient-to-br from-purple-50 via-white to-blue-50">
        <div class="w-full max-w-lg bg-white border border-gray-200 rounded-3xl shadow-lg overflow-hidden">
            <div class="px-8 py-6 border-b border-gray-100">
                <h1 class="text-2xl font-semibold text-gray-900">Yeni Hesap Oluştur</h1>
                <p class="text-sm {{ $theme->color ? 'text-' . $theme->color : 'text-blue-600' }} mt-1">{{ env('APP_NAME') }}
                    <span class="text-gray-500">mağazasında alışveriş
                        yapmak için hızlıca kayıt olun.</span>
                </p>
            </div>
            <div class="px-8 py-6">
                <form action="{{ route('register.submit') }}" method="POST" class="space-y-5" data-auth-form>
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Ad Soyad</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            class="mt-2 w-full px-4 py-3 border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} rounded-xl focus:border-{{ $theme->color ? 'border-' . $theme->color : 'blue-500' }} focus:ring-0">
                        @error('name')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">E-posta</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                            class="mt-2 w-full px-4 py-3 border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} rounded-xl focus:border-{{ $theme->color ? 'border-' . $theme->color : 'blue-500' }} focus:ring-0">
                        @error('email')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Telefon</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" {{ $store->phone_required ? 'required' : '' }}
                            class="mt-2 w-full px-4 py-3 border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} rounded-xl focus:border-{{ $theme->color ? 'border-' . $theme->color : 'blue-500' }} focus:ring-0">
                        @error('phone')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Şifre</label>
                        <input type="password" name="password" required
                            class="mt-2 w-full px-4 py-3 border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} rounded-xl focus:border-{{ $theme->color ? 'border-' . $theme->color : 'blue-500' }} focus:ring-0">
                        @error('password')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Şifre (Tekrar)</label>
                        <input type="password" name="password_confirmation" required
                            class="mt-2 w-full px-4 py-3 border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} rounded-xl focus:border-{{ $theme->color ? 'border-' . $theme->color : 'blue-500' }} focus:ring-0">
                    </div>
                    <button type="submit"
                        class="w-full px-4 py-3 rounded-full {{ $theme->color ? 'bg-' . $theme->color : 'bg-blue-600' }} text-white text-sm font-semibold hover:{{ $theme->color ? 'bg-' . $theme->color . '/30' : 'bg-blue-700' }} hover:{{ $theme->color ? 'text-' . $theme->color : 'text-white' }} transition-colors">
                        Kayıt Ol
                    </button>
                </form>
                <p class="mt-6 text-xs text-gray-400">
                    Kayıt olarak <a href="#"
                        class="underline {{ $theme->color ? 'text-' . $theme->color : 'text-blue-600' }} hover:{{ $theme->color ? 'text-' . $theme->color : 'text-blue-700' }}">kullanım
                        koşullarımızı</a> kabul
                    etmiş
                    olursunuz. E-posta adresinize doğrulama bağlantısı gönderilecektir.
                </p>
                <div class="mt-6 text-sm text-gray-600">
                    Zaten hesabınız var mı?
                    <a href="{{ route('login') }}"
                        class="{{ $theme->color ? 'text-' . $theme->color : 'text-blue-600' }} hover:{{ $theme->color ? 'text-' . $theme->color : 'text-blue-700' }} font-semibold">Giriş
                        Yap</a>
                </div>
            </div>
        </div>
    </div>
@endsection
