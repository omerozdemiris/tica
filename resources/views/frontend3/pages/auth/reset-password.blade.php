@extends($template . '.layouts.app')
@section('title', 'Yeni Şifre Oluştur')
@section('content')
    <div class="min-h-[70vh] flex items-center justify-center px-4 py-16">
        <div class="w-full max-w-md bg-white border border-gray-200 rounded-3xl shadow-lg overflow-hidden">
            <div class="px-8 py-6 border-b border-gray-100">
                <h1 class="text-2xl font-semibold text-gray-900">Yeni Şifre Oluştur</h1>
                <p class="text-sm text-gray-500 mt-1">Lütfen hesabınız için yeni bir şifre belirleyin.</p>
            </div>
            <div class="px-8 py-8">
                <form id="reset-password-form" class="space-y-5">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Yeni Şifre</label>
                        <input type="password" name="password" required
                            class="mt-2 w-full px-4 py-3 border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} rounded-xl focus:border-{{ $theme->color ? 'border-' . $theme->color : 'blue-500' }} focus:ring-0">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Şifre Tekrar</label>
                        <input type="password" name="password_confirmation" required
                            class="mt-2 w-full px-4 py-3 border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} rounded-xl focus:border-{{ $theme->color ? 'border-' . $theme->color : 'blue-500' }} focus:ring-0">
                    </div>

                    <button type="submit" id="reset-btn"
                        class="w-full px-4 py-3 rounded-full {{ $theme->color ? 'bg-' . $theme->color : 'bg-blue-600' }} text-white text-sm font-semibold hover:opacity-90 transition-all">
                        Şifreyi Güncelle ve Giriş Yap
                    </button>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $('#reset-password-form').on('submit', function(e) {
                e.preventDefault();
                const $btn = $('#reset-btn');
                $btn.prop('disabled', true).addClass('opacity-50').text('Güncelleniyor...');

                $.ajax({
                    url: "{{ route('password.update') }}",
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        if (res.success) {
                            showSuccess(res.msg);
                            setTimeout(() => window.location.href = res.redirect, 1500);
                        } else {
                            showError(res.msg);
                            $btn.prop('disabled', false).removeClass('opacity-50').text(
                                'Şifreyi Güncelle ve Giriş Yap');
                        }
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON?.message || 'Bir hata oluştu.';
                        showError(msg);
                        $btn.prop('disabled', false).removeClass('opacity-50').text(
                            'Şifreyi Güncelle ve Giriş Yap');
                    }
                });
            });

            function showSuccess(msg) {
                if (typeof window.showSuccess === 'function') window.showSuccess(msg);
                else if (typeof window.toast === 'function') window.toast({
                    message: msg,
                    type: 'success'
                });
                else alert(msg);
            }

            function showError(msg) {
                if (typeof window.showError === 'function') window.showError(msg);
                else if (typeof window.toast === 'function') window.toast({
                    message: msg,
                    type: 'danger'
                });
                else alert(msg);
            }
        </script>
    @endpush
@endsection
