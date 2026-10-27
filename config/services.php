<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'firebase' => [
        // FIREBASE_PROJECT di .env (nama project Firebase, dipakai sebagai project_id)
        'project_id'   => env('FIREBASE_PROJECT_ID', env('FIREBASE_PROJECT')),
        'credentials'  => env('FIREBASE_CREDENTIALS'),
        'client_email' => env('FIREBASE_CLIENT_EMAIL'),
        'private_key'  => env('FIREBASE_PRIVATE_KEY'),
        'api_key'      => env('FIREBASE_API_KEY'),
    ],

    'biteship' => [
        'api_key' => env('BITESHIP_API_KEY'),
        'base_url' => env('BITESHIP_BASE_URL', 'https://api.biteship.com'),
        'origin_area_id' => env('BITESHIP_ORIGIN_AREA_ID'),
        'origin_postal_code' => env('BITESHIP_ORIGIN_POSTAL_CODE', '40552'),
        'origin_address' => env('BITESHIP_ORIGIN_ADDRESS', 'Jl. Raya Barat No. 802, Cimareme, Kec. Ngamprah, Kabupaten Bandung Barat, Jawa Barat 40552'),
        'origin_contact_name' => env('BITESHIP_ORIGIN_CONTACT_NAME', 'Admin Gudang IMG'),
        'origin_contact_phone' => env('BITESHIP_ORIGIN_CONTACT_PHONE', '081112345678'),
    ],

    'jde' => [
        'host' => env('JDE_HOST'),
        'endpoint' => env('JDE_HOST')
            ? rtrim(env('JDE_HOST'), '/') . '/' . ltrim(env('JDE_ENDPOINT_PUSH_ORDER', '/jderest/v3/orchestrator/ORCH_POS_INSERT_V1'), '/')
            : null,
        'endpoint_push_order' => env('JDE_ENDPOINT_PUSH_ORDER', '/jderest/v3/orchestrator/ORCH_POS_INSERT_V1'),
        'endpoint_item_master' => env('JDE_ENDPOINT_ITEM_MASTER', '/jderest/v3/orchestrator/POS_ITEM_MASTER'),
        'endpoint_base_price' => env('JDE_ENDPOINT_BASE_PRICE', '/jderest/v3/orchestrator/POS_BASE_PRICE'),
        'endpoint_branch' => env('JDE_ENDPOINT_BRANCH', '/jderest/v3/orchestrator/POS_ITEM_BRANCH'),
        'endpoint_customer' => env('JDE_ENDPOINT_CUSTOMER', '/jderest/v3/orchestrator/POS_CUSTOMER_MASTER'),
        'endpoint_price_adjustment' => env('JDE_ENDPOINT_PRICE_ADJUSTMENT', '/jderest/v3/orchestrator/POS_CALCULATE_PRICE_ADJUSTMENT'),
        'username' => env('JDE_USERNAME', 'UDO'),
        'password' => env('JDE_PASSWORD', 'udoroyal'),
        'environment' => env('JDE_ENVIRONMENT', 'JPY920'),
        'role' => env('JDE_ROLE', '*ALL'),
        'business_unit' => env('JDE_BUSINESS_UNIT', '18011'),
        'address_number' => env('JDE_ADDRESS_NUMBER') ? (int) env('JDE_ADDRESS_NUMBER') : 100228,
        'keterangan_prefix' => env('JDE_KETERANGAN_PREFIX', 'WEB-IMG '),
        'timeout' => (int) env('JDE_TIMEOUT', 120),
        'max_retries' => (int) env('JDE_MAX_RETRIES', 5),
        'retry_delay_minutes' => (int) env('JDE_RETRY_DELAY_MINUTES', 5),
        'mock' => (bool) env('JDE_MOCK', false),
    ],

];
