<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Snackzar Application Settings
    |--------------------------------------------------------------------------
    */

    'name' => env('APP_NAME', 'Snackzar'),
    'domain' => env('APP_DOMAIN', 'snackzar.com'),
    'support_email' => env('SUPPORT_EMAIL', 'support@snackzar.com'),

    /*
    |--------------------------------------------------------------------------
    | Google OAuth
    |--------------------------------------------------------------------------
    */
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect_uri' => env('GOOGLE_REDIRECT_URI'),
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Delivery
    |--------------------------------------------------------------------------
    */
    'sms' => [
        'default_country_code' => env('SMS_DEFAULT_COUNTRY_CODE', '91'),
        'otp_template_id' => env('SMS_TEMPLATE_ID_OTP'),
        'order_placed_template_id' => env('SMS_TEMPLATE_ID_ORDER_PLACED'),
        'order_status_template_id' => env('SMS_TEMPLATE_ID_ORDER_STATUS'),
        'india_dlt_principal_entity_id' => env('SMS_DLT_PRINCIPAL_ENTITY_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | ImageKit CDN
    |--------------------------------------------------------------------------
    */
    'imagekit' => [
        'public_key' => env('IMAGEKIT_PUBLIC_KEY'),
        'private_key' => env('IMAGEKIT_PRIVATE_KEY'),
        'url_endpoint' => env('IMAGEKIT_URL_ENDPOINT'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Shiprocket Shipping
    |--------------------------------------------------------------------------
    */
    'shiprocket' => [
        'email' => env('SHIPROCKET_EMAIL'),
        'password' => env('SHIPROCKET_PASSWORD'),
        'token' => env('SHIPROCKET_TOKEN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | MapMyIndia
    |--------------------------------------------------------------------------
    */
    'mapmyindia' => [
        'client_id' => env('MAPMYINDIA_CLIENT_ID'),
        'client_secret' => env('MAPMYINDIA_CLIENT_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */
    'pagination' => [
        'per_page' => 15,
    ],

    /*
    |--------------------------------------------------------------------------
    | OTP Settings
    |--------------------------------------------------------------------------
    */
    'otp' => [
        'length' => 6,
        'expiry_minutes' => 10,
    ],
];
