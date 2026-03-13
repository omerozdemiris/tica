<?php

return [
    'paytr' => [
        'merchant_id' => env('PAYTR_MERCHANT_ID', '359522'),
        'merchant_key' => env('PAYTR_MERCHANT_KEY', 'PLyYCjKRrpUPfhF4'),
        'merchant_salt' => env('PAYTR_MERCHANT_SALT', 'sbqBRap2Y4PLKj1i'),
        'iframe_v2' => [
            'enabled' => true,
            'dark_mode' => false,
        ],
    ],
    'ziraat' => [
        'merchant_id' => env('ZIRAAT_MERCHANT_ID', '000000003635625'),
        'merchant_password' => env('ZIRAAT_MERCHANT_PASSWORD', 'Malhunhatun123*'),
        'is_test_mode' => env('ZIRAAT_TEST_MODE', false),
    ],
];
