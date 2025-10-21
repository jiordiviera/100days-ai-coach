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

    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'base_url' => env('TELEGRAM_API_BASE_URL', 'https://api.telegram.org'),
        'parse_mode' => env('TELEGRAM_DEFAULT_PARSE_MODE', 'HTML'),
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
        'base_uri' => env('GITHUB_API_BASE_URI', 'https://api.github.com/'),
        'template' => [
            'owner' => env('GITHUB_TEMPLATE_OWNER', 'jiordiviera'),
            'repository' => env('GITHUB_TEMPLATE_REPO', '100DaysOfCode-Template'),
            'visibility' => env('GITHUB_TEMPLATE_VISIBILITY', 'public'), // public|private
        ],
        'support' => [
            'owner' => env('GITHUB_SUPPORT_REPO_OWNER'),
            'repository' => env('GITHUB_SUPPORT_REPO'),
            'token' => env('GITHUB_SUPPORT_TOKEN'),
            'default_labels' => array_filter(explode(',', (string) env('GITHUB_SUPPORT_LABELS', 'support,feedback'))),
        ],
    ],

    'wakatime' => [
        'base_uri' => env('WAKATIME_BASE_URI', 'https://wakatime.com/api/v1'),
        'timeout' => (int) env('WAKATIME_TIMEOUT', 10),
    ],

];
