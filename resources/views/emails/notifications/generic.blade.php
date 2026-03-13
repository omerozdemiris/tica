<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
</head>
@php
    $colors = [
        'primary' => '#2563eb',
        'primary_light' => '#dbeafe',
        'primary_dark' => '#1d4ed8',
        'text_on_primary' => '#ffffff',
    ];
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
                            <h1 style="margin:20px 0 4px;font-size:24px;font-weight:700;color:#0f172a;">
                                {{ $title }}</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px 36px 12px;">
                            <p style="margin:0 0 20px;font-size:14px;color:#475569;">{{ $text }}</p>

                            @if ($product)
                                <div
                                    style="margin:20px 0; padding:15px; border:1px solid #e2e8f0; border-radius:15px; display:flex; align-items:center; gap:15px;">
                                    <div style="flex:1;">
                                        <h3 style="margin:0; font-size:16px; color:#0f172a;">{{ $product->title }}</h3>
                                        <p
                                            style="margin:5px 0 0; font-size:14px; font-weight:600; color:{{ $colors['primary'] }};">
                                            {{ number_format($product->price, 2, ',', '.') }} TL
                                        </p>
                                    </div>
                                <div style="text-align:right;">
                                    <a href="{{ $shortLink ?? route('products.show', [$product->id, $product->slug]) }}" target="_blank"
                                        style="display:inline-block;padding:8px 16px;border-radius:99px;font-size:12px;font-weight:600;color:{{ $colors['text_on_primary'] }};text-decoration:none;background-color:{{ $colors['primary'] }};">
                                        Ürünü İncele
                                    </a>
                                </div>
                                </div>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td align="center"
                            style="padding:22px 32px 28px;background-color:#f8fafc;font-size:12px;color:#94a3b8;">
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
