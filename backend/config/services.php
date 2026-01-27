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

    /*
    |--------------------------------------------------------------------------
    | AI Services (RAG Infrastructure)
    |--------------------------------------------------------------------------
    */

    'openai' => [
        'url' => env('OPENAI_API_URL', 'http://localhost:8045/v1'),
        'key' => env('OPENAI_API_KEY', 'dummy-key'),
        'model' => env('OPENAI_MODEL', 'gemini-2.5-flash'),
        'memory_limit' => env('OPENAI_MEMORY_LIMIT', 10),
    ],

    'qdrant' => [
        'url' => env('QDRANT_URL', 'http://localhost:6333'),
        'collection' => env('QDRANT_COLLECTION', 'oikos_knowledge'),
        'timeout' => env('QDRANT_TIMEOUT', 10),
        'score_threshold' => env('QDRANT_SCORE_THRESHOLD', 0.7),
        'limit' => env('QDRANT_LIMIT', 5),
    ],

    'tei' => [
        'url' => env('TEI_URL', 'http://localhost:8080'),
        'timeout' => env('TEI_TIMEOUT', 30),
    ],

];
