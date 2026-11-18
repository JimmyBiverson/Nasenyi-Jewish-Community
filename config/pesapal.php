<?php

return [
    'enabled' => env('PESAPAL_ENABLED', false),
    'environment' => env('PESAPAL_ENVIRONMENT', 'sandbox'),
    'consumer_key' => env('PESAPAL_CONSUMER_KEY'),
    'consumer_secret' => env('PESAPAL_CONSUMER_SECRET'),
    'notification_id' => env('PESAPAL_NOTIFICATION_ID'),
    'public_url' => env('PESAPAL_PUBLIC_URL', env('APP_URL')),
    'currencies' => array_values(array_filter(array_map('trim', explode(',', env('PESAPAL_CURRENCIES', 'UGX'))))),
];
