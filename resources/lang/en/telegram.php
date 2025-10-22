<?php

return [
    'generic' => [
        'friend' => 'maker',
    ],
    'start' => [
        'greeting' => '👋 Hello :name!',
        'intro' => 'I’m the bot companion for 100Days AI Coach.',
        'chat_id' => 'Your Telegram chat ID is <code>:chat_id</code>.',
        'instructions' => 'Copy this ID into the app (Settings → Notifications) to receive reminders here.',
        'help' => 'Type /help to see everything I can do.',
    ],
    'help' => [
        'title' => '🤖 Available commands',
        'lines' => [
            '/start – show the welcome message',
            '/help – list available commands',
            '/language en|fr – choose the language used for notifications',
            '/support – open the support page',
            '/stop – pause Telegram notifications',
        ],
    ],
    'language' => [
        'unsupported' => '❌ I only speak English (en) or French (fr) for now. Try /language en or /language fr.',
        'updated' => '✅ Language updated to :language.',
        'settings_hint' => 'You can change this at any time with /language en or /language fr.',
    ],
    'support' => [
        'message' => '📮 Need help? Our support page is here: :url',
    ],
    'stop' => [
        'message' => "🚫 Telegram notifications paused.\nYou can re-enable them from the app settings whenever you want.",
    ],
    'fallback' => [
        'unknown_command' => 'I didn’t recognise that command. Type /help to see what I can do.',
        'default' => "Thanks! If you need help, use /support or submit the in-app support form.",
    ],
    'languages' => [
        'en' => 'English',
        'fr' => 'French',
    ],
];
