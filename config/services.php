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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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

    'ai' => [
        'groq' => [
            'base_url' => env('AI_GROQ_BASE_URL', 'https://api.groq.com/openai/v1'),
            'api_key' => env('AI_GROQ_API_KEY'),
            'model' => env('AI_GROQ_MODEL', 'openai/gpt-oss-20b'),
            'cost_per_1k_tokens' => (float) env('AI_GROQ_COST_PER_1K', 0),
        ],

        'openai' => [
            'base_url' => env('AI_OPENAI_BASE_URL', 'https://api.openai.com/v1'),
            'api_key' => env('AI_OPENAI_API_KEY'),
            'model' => env('AI_OPENAI_MODEL', 'gpt-4o-mini'),
            'cost_per_1k_tokens' => (float) env('AI_OPENAI_COST_PER_1K', 0),
        ],

        'local' => [
            'base_url' => env('AI_LOCAL_BASE_URL', 'http://127.0.0.1:11434/v1'),
            'api_key' => env('AI_LOCAL_API_KEY'),
            'model' => env('AI_LOCAL_MODEL', 'mistral-7b'),
            'cost_per_1k_tokens' => (float) env('AI_LOCAL_COST_PER_1K', 0),
        ],
    ],

    'github' => [
        'client_id' => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'redirect' => env('GITHUB_REDIRECT_URI', '/auth/github/callback'),
    ],

];
