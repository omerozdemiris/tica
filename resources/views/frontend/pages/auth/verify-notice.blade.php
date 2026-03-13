@extends('frontend.layouts.app')
@section('title', 'E-posta Doğrulaması')
@section('breadcrumb_title', 'E-posta Doğrulaması')
@section('content')
    <div
        class="min-h-[70vh] flex items-center justify-center px-4 py-16 bg-gradient-to-br from-blue-50 via-white to-purple-50">
        <div class="w-full max-w-lg bg-white border border-gray-200 rounded-3xl shadow-lg overflow-hidden">
            <div class="px-8 py-6 border-b border-gray-100">
                <h1 class="text-2xl font-semibold text-gray-900">E-posta Adresini Doğrula</h1>
                <p class="text-sm text-gray-500 mt-1">Hesabınızı aktifleştirmek için e-posta adresinize gönderilen bağlantıya
                    tıklayın.</p>
            </div>
            <div class="px-8 py-6 space-y-6">
                @if (session('status'))
                    <div class="flex items-center gap-3 p-4 rounded-2xl bg-green-50 text-green-700 text-sm">
                        <i class="ri-check-line text-lg"></i>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif
                <div class="space-y-3 text-sm text-gray-600">
                    <p>Sana gönderdiğimiz doğrulama e-postasını bulamıyorsan aşağıdaki formu kullanarak yeniden talep
                        edebilirsin.</p>
                    <p class="text-gray-500">Spam veya Gereksiz klasörünü de kontrol etmeyi unutma.</p>
                </div>
                <form action="{{ route('verification.resend') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kayıtlı E-posta Adresi</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                            class="mt-2 w-full px-4 py-3 border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} rounded-2xl focus:border-{{ $theme->color ? $theme->color : 'blue-500' }} focus:ring-0">
                        @error('email')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit"
                        class="w-full px-4 py-3 rounded-full {{ $theme->color ? 'bg-' . $theme->color : 'bg-blue-600' }} text-white text-sm font-semibold hover:{{ $theme->color ? 'bg-' . $theme->color . '/30' : 'bg-blue-700' }} transition-colors">
                        Doğrulama E-postasını Yeniden Gönder
                    </button>
                </form>
                <div class="text-center text-sm text-gray-600">
                    <p>E-postayı bulduysan <span
                            class="{{ $theme->color ? 'text-' . $theme->color : 'text-blue-600' }}">doğrulama
                            bağlantısına tıkla</span> ve ardından <a href="{{ route('login') }}"
                            class="font-semibold {{ $theme->color ? 'text-' . $theme->color : 'text-blue-600' }} hover:underline">giriş
                            yap</a>.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
