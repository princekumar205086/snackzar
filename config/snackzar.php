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
    | Twilio SMS
    |--------------------------------------------------------------------------
    */
    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'auth_token' => env('TWILIO_AUTH_TOKEN'),
        'phone_number' => env('TWILIO_PHONE_NUMBER'),
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
