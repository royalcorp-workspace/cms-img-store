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

];
