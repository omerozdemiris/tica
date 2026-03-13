<?php

return [
    // Bu isimler admin.* rota adlarının grup/prefix karşılığıdır (admin.<grup>.*).
    'excluded_admin_routes' => [
        'auth',
        'login',
        'deny',
        'logout',
        'login.submit',
        'home',
        'dashboard',
        'dashboard.metrics',
        'account',
        'admin'
    ],
];

