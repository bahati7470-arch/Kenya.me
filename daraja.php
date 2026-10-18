<?php

return [
    'environment' => env('DARAJA_ENV', 'sandbox'),
    'shortcode' => env('DARAJA_SHORTCODE'),
    'consumer_key' => env('DARAJA_CONSUMER_KEY'),
    'consumer_secret' => env('DARAJA_CONSUMER_SECRET'),
    'webhook_token' => env('DARAJA_WEBHOOK_TOKEN'),
    'base_urls' => [
        'sandbox' => 'https://sandbox.safaricom.co.ke',
        'production' => 'https://api.safaricom.co.ke',
    ],
    'callback_base_url' => env('DARAJA_CALLBACK_BASE_URL') ?: env('RENDER_EXTERNAL_URL', env('APP_URL', 'http://localhost')),
    'timeout' => (int) env('DARAJA_TIMEOUT', 15),
];