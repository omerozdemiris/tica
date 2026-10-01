<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-posta Doğrulama</title>
</head>

@php
    $colors = $themeColors ?? [
        'primary' => '#2563eb',
        'primary_light' => '#dbeafe',
        'primary_dark' => '#1d4ed8',
        'text_on_primary' => '#ffffff',
    ];
    $mailImage = env('APP_URL') . '/assets/img/mail.png';
@endphp

<body
    style="margin:0;padding:0;background-color:#f1f5f9;font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="520" cellspacing="0" cellpadding="0" border="0"
                    style="background-color:#ffffff;border-radius:28px;overflow:hidden;box-shadow:0 25px 60px rgba(15,23,42,0.15);">
                    <tr>
                        <td align="center" style="padding:36px 32px;background-color:{{ $colors['primary_light'] }};">
                            <div
                                style="width:64px;height:64px;border-radius:20px;display:flex;align-items:center;justify-content:center;background-color:{{ $colors['primary'] }};color:{{ $colors['text_on_primary'] }};">
                                <img src="{{ $mailImage }}" style="width: 50px; height: 50px">
                            </div>
                            <h1 style="margin:20px 0 4px;font-size:24px;font-weight:700;color:#0f172a;">E-posta Adresini
                                Doğrula</h1>
                            <p style="margin:0;font-size:14px;color:#475569;">Hesabını güvence altına almak için
                                aşağıdaki butona tıklayarak e-postanı doğrula.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px 36px 12px;">
                            <p style="margin:0 0 14px;font-size:15px;color:#1f2937;">Merhaba <span
                                    style="font-weight:600;color:{{ $colors['primary_dark'] }};">{{ $user->name ?? $user->email }}</span>,
                                {{ config('app.name') }} ailesine hoş geldin.</p>
                            <p style="margin:0 0 20px;font-size:14px;color:#475569;">Butona tıkladığında hesabın aktif
                                hale gelecek ve alışveriş yapmaya devam edebileceksin.</p>
                            <div style="text-align:center;margin:28px 0;">
                                <a href="{{ $verificationUrl }}" target="_blank" rel="noopener"
                                    style="display:inline-block;padding:14px 34px;border-radius:999px;font-size:14px;font-weight:600;color:{{ $colors['text_on_primary'] }};text-decoration:none;background-color:{{ $colors['primary'] }};box-shadow:0 15px 35px rgba(15,23,42,0.15);">
                                    E-postamı Doğrula
                                </a>
                            </div>
                            <div
                                style="margin:0 0 18px;padding:16px 18px;border-radius:18px;background-color:#f8fafc;border:1px solid #e2e8f0;">
                                <p style="margin:0 0 6px;font-size:13px;font-weight:600;color:#0f172a;">Bağlantı sorun
                                    mu çıkarıyor?</p>
                                <p style="margin:0 0 10px;font-size:12px;color:#475569;">Aşağıdaki linki kopyalayıp
                                    tarayıcına yapıştırarak da doğrulama yapabilirsin.</p>
                                <p style="margin:0;font-size:11px;color:#94a3b8;word-break:break-word;">
                                    {{ $verificationUrl }}</p>
                            </div>
                            <p style="margin:0;font-size:12px;color:#94a3b8;">Bu bağlantı 24 saat geçerlidir. İsteği sen
                                oluşturmadıysan bu e-postayı yok sayabilirsin.</p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center"
                            style="padding:22px 32px 28px;background-color:#f8fafc;font-size:12px;color:#94a3b8;">
                            <p style="margin:0 0 4px;">Soruların için <a
                                    href="mailto:{{ config('mail.from.address') }}"
                                    style="color:{{ $colors['primary_dark'] }};text-decoration:none;font-weight:600;">bize
                                    ulaş</a>.</p>
                            <p style="margin:0;">&copy; {{ date('Y') }} {{ config('app.name') }}. Tüm hakları
                                saklıdır.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
