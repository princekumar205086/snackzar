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

    /*
    |--------------------------------------------------------------------------
    | SEO Configuration - Global and Regional Settings
    |--------------------------------------------------------------------------
    */
    'seo' => [
        'brand_name' => env('SEO_BRAND_NAME', 'Snackzar'),
        'phone' => env('SEO_PHONE', '+91-XXXXXX'),

        // Canonical domain
        'canonical_domain' => env('APP_DOMAIN', 'snackzar.com'),
        'canonical_scheme' => env('APP_SCHEME', 'https'),

        // Social media
        'social_media' => [
            'facebook' => 'https://facebook.com/snackzar',
            'instagram' => 'https://instagram.com/snackzar',
            'twitter' => 'https://twitter.com/snackzar',
            'youtube' => 'https://youtube.com/snackzar',
        ],

        // Verification codes
        'google_verification' => env('GOOGLE_SITE_VERIFICATION', ''),
        'google_analytics' => env('GOOGLE_ANALYTICS_ID', ''),
        'google_search_console' => env('GOOGLE_SEARCH_CONSOLE', ''),
        'bing_verification' => env('BING_WEBMASTER_VERIFICATION', ''),

        // Regio nal/Language settings for hreflang
        'regions' => [
            'in' => ['language' => 'en-IN', 'country' => 'IN', 'currency' => 'INR', 'default' => true],
            'us' => ['language' => 'en-US', 'country' => 'US', 'currency' => 'USD'],
            'gb' => ['language' => 'en-GB', 'country' => 'GB', 'currency' => 'GBP'],
            'ae' => ['language' => 'en-AE', 'country' => 'AE', 'currency' => 'AED'],
            'sg' => ['language' => 'en-SG', 'country' => 'SG', 'currency' => 'SGD'],
        ],

        // Sitemap settings
        'sitemap_cache_hours' => 24,
        'sitemap_include_products' => true,
        'sitemap_include_categories' => true,
        'sitemap_include_blog' => true,
        'sitemap_include_cities' => true,
        'sitemap_chunk_size' => env('SEO_SITEMAP_CHUNK_SIZE', 45000),

        // Programmatic SEO scale settings
        'programmatic' => [
            'target_indexable_pages' => env('SEO_TARGET_INDEXABLE_PAGES', 150000),
            'keyword_universe_size' => env('SEO_KEYWORD_UNIVERSE_SIZE', 250000),
            'indian_city_target' => env('SEO_INDIAN_CITY_TARGET', 420),
            'global_city_target' => env('SEO_GLOBAL_CITY_TARGET', 520),
        ],

        // JSON-LD Schema
        'enable_schema_org' => true,
        'enable_breadcrumb_schema' => true,
        'enable_product_schema' => true,
        'enable_review_schema' => true,

        // PWA Settings
        'enable_pwa' => true,
        'pwa_service_worker' => '/service-worker.js',
        'pwa_manifest' => '/manifest.json',
        'pwa_offline_page' => '/offline.html',

        // Performance
        'enable_lazy_loading' => true,
        'enable_image_optimization' => true,
        'cache_ttl_minutes' => 60,
    ],
];
