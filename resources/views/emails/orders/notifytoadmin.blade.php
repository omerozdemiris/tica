<!DOCTYPE html>

<html lang="tr">



<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Yeni Sipariş Bildirimi</title>

</head>



@php

    $colors = $themeColors ?? [
        'primary' => '#2563eb',

        'primary_light' => '#dbeafe',

        'primary_dark' => '#1d4ed8',

        'text_on_primary' => '#ffffff',
    ];

    $mailImage = env('APP_URL') . '/assets/img/mail.png';

    $appName = config('app.name');

    $appUrl = config('app.url');

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

                            <h1 style="margin:20px 0 4px;font-size:24px;font-weight:700;color:#0f172a;">Yeni Sipariş

                                Alındı</h1>

                            <p style="margin:0;font-size:14px;color:#475569;">Yeni bir sipariş oluşturuldu ve işleme

                                alınmayı bekliyor.</p>

                        </td>

                    </tr>

                    <tr>

                        <td style="padding:32px 36px 12px;">

                            <div style="display:flex;flex-wrap:wrap;gap:16px;margin-bottom:24px;">

                                <div
                                    style="flex:1;min-width:200px;padding:20px;border-radius:18px;background-color:#f8fafc;border:1px solid #e2e8f0;">

                                    <p style="margin:0;font-size:12px;text-transform:uppercase;color:#94a3b8;">Sipariş

                                        No

                                    </p>

                                    <p style="margin:6px 0 0;font-size:18px;font-weight:600;color:#111827;">

                                        #{{ $order->order_number }}</p>

                                </div>

                                <div
                                    style="flex:1;min-width:200px;padding:20px;border-radius:18px;background-color:#f8fafc;border:1px solid #e2e8f0;">

                                    <p style="margin:0;font-size:12px;text-transform:uppercase;color:#94a3b8;">Toplam

                                        Tutar

                                    </p>

                                    <p style="margin:6px 0 0;font-size:18px;font-weight:600;color:#111827;">

                                        {{ number_format((float) $order->total, 2, ',', '.') }} ₺</p>

                                </div>

                                <div
                                    style="flex:1;min-width:200px;padding:20px;border-radius:18px;background-color:#f8fafc;border:1px solid #e2e8f0;">

                                    <p style="margin:0;font-size:12px;text-transform:uppercase;color:#94a3b8;">Ödeme

                                        Yöntemi

                                    </p>

                                    <p style="margin:6px 0 0;font-size:18px;font-weight:600;color:#111827;">

                                        @if ($order->method == 'card')
                                            Kredi Kartı
                                        @elseif ($order->method == 'wire')
                                            Havale / EFT
                                        @else
                                            {{ $order->method }}
                                        @endif

                                    </p>

                                </div>

                            </div>



                            <div
                                style="margin:0 0 20px;padding:16px 18px;border-radius:18px;background-color:#f8fafc;border:1px solid #e2e8f0;">

                                <p style="margin:0 0 10px;font-size:13px;font-weight:600;color:#0f172a;">Müşteri

                                    Bilgileri

                                </p>

                                <p style="margin:4px 0;font-size:14px;color:#475569;">

                                    <strong>Ad Soyad:</strong> {{ $order->customer_name ?? 'Belirtilmedi' }}

                                </p>

                                <p style="margin:4px 0;font-size:14px;color:#475569;">

                                    <strong>E-posta:</strong> {{ $order->customer_email ?? 'Belirtilmedi' }}

                                </p>

                                <p style="margin:4px 0;font-size:14px;color:#475569;">

                                    <strong>Telefon:</strong> {{ $order->customer_phone ?? 'Belirtilmedi' }}

                                </p>

                                @if ($order->shipping_address)
                                    <p style="margin:8px 0 0;font-size:14px;color:#475569;">

                                        <strong>Teslimat Adresi:</strong><br>

                                        <span style="white-space:pre-line;">{{ $order->shipping_address }}</span>

                                    </p>
                                @endif

                            </div>



                            <h3 style="font-size:16px;margin:0 0 12px 0;color:#1e293b;font-weight:700;">Sipariş Özeti

                            </h3>

                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation"
                                style="border-collapse:collapse;font-size:14px;margin-bottom:24px;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">

                                <thead>

                                    <tr style="background-color:#f8fafc;">

                                        <th align="left" style="padding:10px 16px;color:#64748b;font-weight:600;">Ürün

                                        </th>

                                        <th align="center" style="padding:10px 16px;color:#64748b;font-weight:600;">Adet

                                        </th>

                                        <th align="right" style="padding:10px 16px;color:#64748b;font-weight:600;">

                                            Fiyat</th>

                                    </tr>

                                </thead>

                                <tbody>

                                    @php
                                        $erpProducts = collect();
                                    @endphp

                                    @foreach ($order->items as $item)
                                        <tr>
                                            <td style="padding:10px 16px;border-top:1px solid #e2e8f0;">
                                                @php
                                                    $displayName =
                                                        $item->product?->title ?? $item->product_id;
                                                @endphp
                                                <div style="font-weight: 600;">
                                                    {{ $displayName }}</div>
                                                @php
                                                    $variants = $item->variant_ids
                                                        ? $item->variants()
                                                        : ($item->variant
                                                            ? collect([$item->variant])
                                                            : collect());
                                                @endphp
                                                @if ($variants->isNotEmpty())
                                                    <div style="margin-top: 4px;">
                                                        @foreach ($variants as $v)
                                                            @php
                                                                $term = $v->term;
                                                                $colorMatch =
                                                                    isset($term->value) &&
                                                                    str_starts_with($term->value, '#')
                                                                        ? $term->value
                                                                        : null;
                                                            @endphp
                                                            <div
                                                                style="font-size:11px;color:#64748b;margin-bottom:2px;">
                                                                <strong>{{ $v->attribute?->name ?? 'Seçenek' }}:</strong>
                                                                @if ($colorMatch)
                                                                    <span
                                                                        style="display:inline-block;width:8px;height:8px;border-radius:50%;background-color:{{ $colorMatch }};border:1px solid #e2e8f0;vertical-align:middle;margin:0 2px;"></span>
                                                                @endif
                                                                {{ $term->name ?? 'N/A' }}
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </td>
                                            <td align="center" style="padding:10px 16px;border-top:1px solid #e2e8f0;">
                                                {{ $item->quantity ?? 1 }}
                                            </td>
                                            <td align="right" style="padding:10px 16px;border-top:1px solid #e2e8f0;">
                                                {{ number_format((float) ($item->total ?? 0), 2, ',', '.') }} ₺
                                            </td>
                                        </tr>
                                    @endforeach

                                </tbody>

                            </table>



                            @if ($order->notes)
                                <div
                                    style="margin:0 0 20px;padding:16px 18px;border-radius:18px;background-color:#fef3c7;border:1px solid #fde68a;">

                                    <p style="margin:0 0 6px;font-size:13px;font-weight:600;color:#92400e;">Sipariş Notu

                                    </p>

                                    <p style="margin:0;font-size:12px;color:#78350f;white-space:pre-line;">

                                        {{ $order->notes }}</p>

                                </div>
                            @endif



                            <div style="text-align:center;margin:28px 0;">

                                <a href="{{ $appUrl }}/admin/orders/{{ $order->id }}" target="_blank"
                                    rel="noopener"
                                    style="display:inline-block;padding:14px 34px;border-radius:999px;font-size:14px;font-weight:600;color:{{ $colors['text_on_primary'] }};text-decoration:none;background-color:{{ $colors['primary'] }};box-shadow:0 15px 35px rgba(15,23,42,0.15);">

                                    Siparişi Görüntüle

                                </a>

                            </div>



                            <p style="margin:0;font-size:12px;color:#94a3b8;">Sipariş tarihi:

                                {{ $order->created_at->format('d.m.Y H:i') }}</p>

                        </td>

                    </tr>

                    <tr>

                        <td align="center"
                            style="padding:22px 32px 28px;background-color:#f8fafc;font-size:12px;color:#94a3b8;">

                            <p style="margin:0 0 4px;">Sorularınız için <a
                                    href="mailto:{{ config('mail.from.address') }}"
                                    style="color:{{ $colors['primary_dark'] }};text-decoration:none;font-weight:600;">bize

                                    ulaşabilirsiniz</a>.</p>

                            <p style="margin:0;">&copy; {{ date('Y') }} {{ $appName }}. Tüm hakları saklıdır.

                            </p>

                        </td>

                    </tr>

                </table>

            </td>

        </tr>

    </table>

</body>



</html>
