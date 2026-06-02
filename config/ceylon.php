<?php

return [
    'otp_bypass' => (bool) env('OTP_BYPASS', true),
    'otp_ttl_minutes' => (int) env('OTP_TTL_MINUTES', 5),
    'admin' => [
        'username' => env('ADMIN_USERNAME', 'admin'),
        'password' => env('ADMIN_PASSWORD', 'admin123'),
    ],
];
