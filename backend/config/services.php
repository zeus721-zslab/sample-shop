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

    'payment_gateway' => env('PAYMENT_GATEWAY', 'mock'),

    'portone' => [
        'api_secret'  => env('PORTONE_API_SECRET'),
        'channel_key' => env('PORTONE_CHANNEL_KEY'),
    ],

    'elasticsearch' => [
        'host'  => env('ELASTICSEARCH_HOST', 'http://elasticsearch:9200'),
        'index' => env('ELASTICSEARCH_PRODUCTS_INDEX', 'zslab_products'),
    ],

    // Social OAuth — 라우트만 설계, 실제 연동은 추후
    'kakao' => [
        'client_id'     => env('KAKAO_CLIENT_ID'),
        'client_secret' => env('KAKAO_CLIENT_SECRET'),
        'redirect'      => env('KAKAO_REDIRECT_URI'),
    ],

    'naver' => [
        'client_id'     => env('NAVER_CLIENT_ID'),
        'client_secret' => env('NAVER_CLIENT_SECRET'),
        'redirect'      => env('NAVER_REDIRECT_URI'),
    ],

    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT_URI'),
    ],

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

];
