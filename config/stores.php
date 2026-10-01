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
        'merchant_id' => env('ZIRAAT_MERCHANT_ID', '191360440'),
        'merchant_password' => env('ZIRAAT_MERCHANT_PASSWORD', 'macroturk77*+'),

        'store_number' => env('ZIRAAT_STORE_NUMBER', '000000004155542'),
        'terminal_id' => env('ZIRAAT_TERMINAL_ID', 'V0360440'),

        'is_test_mode' => env('ZIRAAT_TEST_MODE', false),
    ],
];
