@extends('frontend.layouts.app')
@section('title', 'Şifremi Unuttum')
@section('content')
    <div class="min-h-[50vh] flex items-center justify-center px-4 py-8">
        <div class="w-full max-w-md bg-white border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} rounded-2xl shadow-sm overflow-hidden">
            <div class="px-8 py-6 border-b border-gray-100">
                <h1 class="text-2xl font-semibold text-gray-900">Şifremi Unuttum</h1>
                <p class="text-sm text-gray-500 mt-1">Şifrenizi sıfırlamak için kayıtlı e-posta adresinizi girin.</p>
            </div>
            <div class="px-8 py-8">
                <div id="search-step">
                    <div class="space-y-5">
                        <div class="flex justify-center mb-2">
                            <div class="inline-flex items-center gap-1 bg-gray-100 rounded-full p-1 text-xs"
                                id="forgot-method-switch">
                                <button type="button" data-forgot-method="email"
                                    class="flex items-center gap-1 px-3 py-1 rounded-full bg-white shadow text-gray-900 font-medium">
                                    <i class="ri-mail-line text-sm"></i>
                                    <span>E-posta</span>
                                </button>
                                <button type="button" data-forgot-method="phone"
                                    class="flex items-center gap-1 px-3 py-1 rounded-full text-gray-500 font-medium">
                                    <i class="ri-smartphone-line text-sm"></i>
                                    <span>Telefon</span>
                                </button>
                            </div>
                        </div>
                        <div id="forgot-email-wrapper">
                            <label class="block text-sm font-medium text-gray-700">E-posta Adresi</label>
                            <input type="email" id="forgot-email" placeholder="ornek@mail.com"
                                class="mt-2 w-full px-4 py-3 border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} rounded-xl focus:border-{{ $theme->color ? $theme->color : 'blue-500' }} focus:ring-0">
                        </div>
                        <div id="forgot-phone-wrapper" class="hidden">
                            <label class="block text-sm font-medium text-gray-700">Telefon Numarası</label>
                            <input type="text" id="forgot-phone" placeholder="5XX XXX XX XX"
                                class="mt-2 w-full px-4 py-3 border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} rounded-xl focus:border-{{ $theme->color ? $theme->color : 'blue-500' }} focus:ring-0">
                        </div>
                        <button type="button" onclick="searchUser()" id="search-btn"
                            class="w-full px-4 py-3 rounded-full {{ $theme->color ? 'bg-' . $theme->color : 'bg-blue-600' }} text-white text-sm font-semibold hover:opacity-90 transition-all flex items-center justify-center gap-2">
                            <span>Kullanıcıyı Bul</span>
                            <i class="ri-search-line"></i>
                        </button>
                        <button type="button" id="send-sms-btn"
                            class="hidden w-full px-4 py-3 rounded-full {{ $theme->color ? 'bg-' . $theme->color : 'bg-blue-600' }} text-white text-sm font-semibold hover:opacity-90 transition-all flex items-center justify-center gap-2"
                            data-reset-sms-url="{{ route('password.email') }}">
                            <span>SMS ile Bağlantı Gönder</span>
                            <i class="ri-smartphone-line"></i>
                        </button>
                    </div>
                </div>
                <div id="confirm-step" class="hidden animate__animated animate__fadeIn">
                    <div class="flex flex-col items-center text-center space-y-4">
                        <div id="user-avatar"
                            class="w-20 h-20 rounded-full {{ $theme->color ? 'bg-' . $theme->color : 'bg-blue-600' }} text-white flex items-center justify-center text-2xl font-bold shadow-lg">
                        </div>
                        <div class="space-y-1">
                            <h3 id="user-name" class="text-lg font-bold text-gray-900"></h3>
                            <p id="user-email" class="text-sm text-gray-500"></p>
                        </div>

                        <div class="bg-gray-50 p-3 rounded-xl w-full border border-gray-100">
                            <p class="text-sm font-medium text-gray-600">Bu hesap size mi ait?</p>
                        </div>

                        <div class="w-full grid grid-cols-2 gap-3">
                            <button type="button" onclick="resetSteps()"
                                class="px-4 py-3 rounded-full border border-gray-200 text-gray-600 text-sm font-semibold hover:bg-gray-50 transition-all">
                                Vazgeç
                            </button>
                            <button type="button" onclick="sendResetLink()" id="send-btn"
                                class="px-4 py-3 rounded-full {{ $theme->color ? 'bg-' . $theme->color : 'bg-blue-600' }} text-white text-sm font-semibold hover:opacity-90 transition-all">
                                Mail Gönder
                            </button>
                        </div>
                    </div>
                </div>
                <div id="success-step" class="hidden animate__animated animate__fadeIn">
                    <div class="text-center space-y-4">
                        <div
                            class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto text-3xl">
                            <i class="ri-checkbox-circle-line"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900">Mail Gönderildi!</h2>
                        <p class="text-sm text-gray-500">Şifre sıfırlama bağlantısını e-posta adresinize gönderdik. Lütfen
                            gelen kutunuzu (ve gereksiz kutusunu) kontrol edin.</p>
                        <a href="{{ route('login') }}"
                            class="inline-block px-8 py-3 rounded-full {{ $theme->color ? 'bg-' . $theme->color : 'bg-blue-600' }} text-white text-sm font-semibold hover:opacity-90 transition-all">
                            Giriş Sayfasına Dön
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            const themeColor = "{{ $theme->color ?? 'blue-600' }}";
            const EMAIL_COOLDOWN_KEY = 'forgot_reset_email_expires';

            function initEmailCooldown() {
                if (!window.sessionStorage) return;
                const expires = sessionStorage.getItem(EMAIL_COOLDOWN_KEY);
                if (!expires) return;
                const diff = parseInt(expires, 10) - Date.now();
                if (diff > 0) {
                    const remaining = Math.ceil(diff / 1000);
                    applyEmailCooldown(remaining);
                } else {
                    sessionStorage.removeItem(EMAIL_COOLDOWN_KEY);
                }
            }

            function applyEmailCooldown(remainingSeconds) {
                const $btn = $('#send-btn');
                if (!$btn.length) return;

                let seconds = remainingSeconds;
                const originalText = $btn.data('original-text') || $btn.text();
                if (!$btn.data('original-text')) {
                    $btn.data('original-text', originalText);
                }

                function tick() {
                    if (seconds <= 0) {
                        $btn.prop('disabled', false).removeClass('opacity-50').text(
                            originalText);
                        if (window.sessionStorage) {
                            sessionStorage.removeItem(EMAIL_COOLDOWN_KEY);
                        }
                        return;
                    }

                    $btn.prop('disabled', true).addClass('opacity-50').text(seconds +
                        ' sn sonra tekrar gönderebilirsiniz');
                    seconds--;
                    setTimeout(tick, 1000);
                }

                tick();
            }

            function searchUser() {
                const email = $('#forgot-email').val();
                if (!email) return showToast('Lütfen e-posta adresinizi girin.', 'danger');

                $('#search-btn').prop('disabled', true).addClass('opacity-50').find('span').text('Aranıyor...');

                $.ajax({
                    url: "{{ route('password.search') }}",
                    type: 'POST',
                    data: {
                        email: email,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(res) {
                        if (res.success) {
                            $('#user-avatar').text(res.user.initials);
                            $('#user-name').text(res.user.name);
                            $('#user-email').text(res.user.email);

                            $('#search-step').addClass('hidden');
                            $('#confirm-step').removeClass('hidden');
                        } else {
                            showToast(res.msg, 'danger');
                        }
                    },
                    error: function() {
                        showToast('Bir hata oluştu.', 'danger');
                    },
                    complete: function() {
                        $('#search-btn').prop('disabled', false).removeClass('opacity-50').find('span').text(
                            'Kullanıcıyı Bul');
                    }
                });
            }

            function sendResetLink() {
                const email = $('#user-email').text();
                $('#send-btn').prop('disabled', true).addClass('opacity-50').text('Gönderiliyor...');

                $.ajax({
                    url: "{{ route('password.email') }}",
                    type: 'POST',
                    data: {
                        email: email,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(res) {
                        if (res.success) {
                            $('#confirm-step').addClass('hidden');
                            $('#success-step').removeClass('hidden');
                            if (res.cooldown && res.remaining && window.sessionStorage) {
                                const expiresAt = Date.now() + (res.remaining * 1000);
                                sessionStorage.setItem(EMAIL_COOLDOWN_KEY, expiresAt);
                            }
                        } else {
                            showToast(res.msg, 'danger');
                            if (res.cooldown && res.remaining && window.sessionStorage) {
                                const expiresAt = Date.now() + (res.remaining * 1000);
                                sessionStorage.setItem(EMAIL_COOLDOWN_KEY, expiresAt);
                                applyEmailCooldown(res.remaining);
                            }
                        }
                    },
                    error: function() {
                        showToast('Bir hata oluştu.', 'danger');
                    },
                    complete: function() {
                        if (!window.sessionStorage || !sessionStorage.getItem(EMAIL_COOLDOWN_KEY)) {
                            $('#send-btn').prop('disabled', false).removeClass('opacity-50').text('Mail Gönder');
                        }
                    }
                });
            }

            function resetSteps() {
                $('#confirm-step').addClass('hidden');
                $('#search-step').removeClass('hidden');
                $('#forgot-email').val('');
            }

            function showToast(msg, type) {
                if (typeof window.toast === 'function') {
                    window.toast({
                        message: msg,
                        type: type
                    });
                } else if (typeof window.showError === 'function' && type === 'danger') {
                    window.showError(msg);
                } else if (typeof window.showSuccess === 'function' && type === 'success') {
                    window.showSuccess(msg);
                } else {
                    alert(msg);
                }
            }

            $(document).ready(function() {
                initEmailCooldown();
            });
        </script>
    @endpush
@endsection
