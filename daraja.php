<?php

return [
    'environment' => env('DARAJA_ENV', 'sandbox'),
    'base_urls' => [
        'sandbox' => 'https://sandbox.safaricom.co.ke',
        'production' => 'https://api.safaricom.co.ke',
    ],
    'callback_base_url' => env('DARAJA_CALLBACK_BASE_URL') ?: env('RENDER_EXTERNAL_URL', env('APP_URL', 'http://localhost')),
    'timeout' => (int) env('DARAJA_TIMEOUT', 15),
];