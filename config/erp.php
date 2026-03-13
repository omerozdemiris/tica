<?php

return [
    /*
    |--------------------------------------------------------------------------
    | EasyTrade ERP API Configuration
    |--------------------------------------------------------------------------
    |
    | Tüm ERP entegrasyonu için temel ayarlar. Ortam bazlı olarak .env
    | dosyasından okunur. Buradaki değerlerin hiçbiri repoya gizli bilgi
    | olarak girilmemelidir.
    |
    */

    'enabled' => (bool) env('ERP_ENABLED', false),

    // Sadece frontend ürün/kategori okuma tarafını aktif etmek için.
    'frontend_enabled' => (bool) env('ERP_FRONTEND_ENABLED', false),

    // Sipariş oluşturma entegrasyonunu (ERP'ye POST) ayrı flag ile kontrol et.
    'orders_enabled' => (bool) env('ERP_ORDERS_ENABLED', false),

    'base_url' => env('ERP_BASE_URL', 'https://pro.easytradetr.com'),

    // Auth için kullanılacak bilgiler (opsiyonel; ilk etapta sabit token kullanılabilir).
    'auth' => [
        'email' => env('ERP_AUTH_EMAIL'),
        'password' => env('ERP_AUTH_PASSWORD'),
        'device_name' => env('ERP_AUTH_DEVICE_NAME', 'Laravel ECommerce'),
    ],

    // Doğrudan kullanılacak erişim token'ı. Tercihen .env'de tutulur.
    'api_token' => env('ERP_API_TOKEN'),

    // Şirket ve şube kimlikleri header üzerinden gönderilir.
    'company_id' => env('ERP_COMPANY_ID'),
    'branch_id' => env('ERP_BRANCH_ID'),

    // Varsayılan listeleme ayarları.
    'defaults' => [
        'per_page' => env('ERP_DEFAULT_PER_PAGE', 15),
    ],
];

