<x-mail::message>
# Merhaba {{ $user->name }},

Hesabınız için bir şifre sıfırlama talebi aldık. Şifrenizi sıfırlamak için aşağıdaki butona tıklayabilirsiniz:

<x-mail::button :url="route('password.reset', $token)">
Şifremi Sıfırla
</x-mail::button>

Eğer bu talebi siz yapmadıysanız, bu e-postayı dikkate almayınız.

Teşekkürler,<br>
{{ config('app.name') }}
</x-mail::message>

