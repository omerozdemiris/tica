@extends('frontend.layouts.app')
@section('title', 'Giriş Yap')
@section('breadcrumb_title', 'Giriş Yap')
@section('content')
    <div class="min-h-[70vh] flex items-center justify-center px-4 py-12 bg-gray-50/40">
        <div class="w-full max-w-md bg-white border border-gray-200/60 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200/60">
                <h1 class="text-xl font-medium text-gray-900">Giriş Yap</h1>
                <p class="text-xs text-gray-500 mt-1">Hesabınıza erişmek için aşağıdaki bilgileri doldurun.</p>
            </div>
            <div class="px-6 py-5">
                <form action="{{ route('login.submit') }}" method="POST" class="space-y-4" data-auth-form>
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-700">E-posta</label>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                            class="mt-1.5 w-full px-3 py-2.5 text-sm border border-gray-200/60 focus:border-{{ $theme->color ? $theme->color : 'blue-500' }} focus:ring-0">
                        @error('email')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-medium text-gray-700">Şifre</label>
                            <a href="{{ route('password.request') }}"
                                class="text-xs {{ $theme->color ? 'text-' . $theme->color : 'blue-600' }} hover:{{ $theme->color ? 'text-' . $theme->color : 'blue-700' }}">Şifremi
                                unuttum</a>
                        </div>
                        <input type="password" name="password" required
                            class="mt-1.5 w-full px-3 py-2.5 text-sm border border-gray-200/60 focus:border-{{ $theme->color ? $theme->color : 'blue-500' }} focus:ring-0">
                    </div>
                    <div class="flex items-center justify-between text-xs text-gray-600">
                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" name="remember" value="1">
                            Beni hatırla
                        </label>
                        <a href="{{ route('register') }}"
                            class="{{ $theme->color ? 'text-' . $theme->color : 'blue-600' }} hover:{{ $theme->color ? 'text-' . $theme->color : 'blue-700' }}">Hesap
                            oluştur</a>
                    </div>
                    <button type="submit"
                        class="w-full px-4 py-2.5 {{ $theme->color ? 'bg-' . $theme->color : 'bg-blue-600' }} text-white text-xs font-medium hover:opacity-90 transition-colors">
                        Giriş Yap
                    </button>
                </form>
                <p class="mt-5 text-[11px] text-gray-600">
                    Oturum açarak <a href="#"
                        class="underline {{ $theme->color ? 'text-' . $theme->color : 'blue-600' }} hover:{{ $theme->color ? 'text-' . $theme->color : 'blue-700' }}">kullanım
                        koşullarımızı</a> ve <a href="#"
                        class="underline {{ $theme->color ? 'text-' . $theme->color : 'blue-600' }} hover:{{ $theme->color ? 'text-' . $theme->color : 'blue-700' }}">gizlilik
                        politikamızı</a> kabul etmiş
                    olursunuz.
                </p>
            </div>
        </div>
    </div>
@endsection