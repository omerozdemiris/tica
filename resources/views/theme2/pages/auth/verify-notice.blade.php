@extends('frontend.layouts.app')
@section('title', 'E-posta Doğrulaması')
@section('breadcrumb_title', 'E-posta Doğrulaması')
@section('content')
    <div
        class="min-h-[70vh] flex items-center justify-center px-4 py-12 bg-gray-50/40">
        <div class="w-full max-w-lg bg-white border border-gray-200/60 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200/60">
                <h1 class="text-xl font-medium text-gray-900">E-posta Adresini Doğrula</h1>
                <p class="text-xs text-gray-500 mt-1">Hesabınızı aktifleştirmek için e-posta adresinize gönderilen bağlantıya
                    tıklayın.</p>
            </div>
            <div class="px-6 py-5 space-y-5">
                @if (session('status'))
                    <div class="flex items-center gap-3 p-3 bg-green-50 text-green-700 text-xs border border-green-200/60">
                        <i class="ri-check-line text-base"></i>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif
                <div class="space-y-2 text-xs text-gray-600">
                    <p>Sana gönderdiğimiz doğrulama e-postasını bulamıyorsan aşağıdaki formu kullanarak yeniden talep
                        edebilirsin.</p>
                    <p class="text-gray-500">Spam veya Gereksiz klasörünü de kontrol etmeyi unutma.</p>
                </div>
                <form action="{{ route('verification.resend') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Kayıtlı E-posta Adresi</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                            class="mt-1.5 w-full px-3 py-2.5 text-sm border border-gray-200/60 focus:border-{{ $theme->color ? $theme->color : 'blue-500' }} focus:ring-0">
                        @error('email')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit"
                        class="w-full px-4 py-2.5 {{ $theme->color ? 'bg-' . $theme->color : 'bg-blue-600' }} text-white text-xs font-medium hover:opacity-90 transition-colors">
                        Doğrulama E-postasını Yeniden Gönder
                    </button>
                </form>
                <div class="text-center text-xs text-gray-600">
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
