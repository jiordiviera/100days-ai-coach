<?php

return [
    'generic' => [
        'friend' => 'maker',
    ],
    'start' => [
        'greeting' => '👋 Hello :name!',
        'intro' => 'I’m the bot companion for 100Days AI Coach.',
        'chat_id' => 'Your Telegram chat ID is <code>:chat_id</code>.',
        'instructions' => 'Tap the button below to link your account automatically. You can also paste the ID in Settings → Notifications if you prefer.',
        'help' => 'Type /help to see everything I can do.',
    ],
    'help' => [
        'title' => '🤖 Available commands',
        'lines' => [
            '/start – show the welcome message',
            '/help – list available commands',
            '/signup – open the sign-up page with Telegram prefilled',
            '/language en|fr – choose the language used for notifications',
            '/support – open the support page',
            '/stop – pause Telegram notifications',
        ],
    ],
    'language' => [
        'unsupported' => '❌ I only speak English (en) or French (fr) for now. Try /language en or /language fr.',
        'updated' => '✅ Language updated to :language.',
        'settings_hint' => 'You can change this at any time with /language en or /language fr.',
        'pick' => 'Select your preferred language:',
    ],
    'link' => [
        'generated' => "Tap the button below to link your Telegram chat. If it doesn't open, visit :url and enter your chat ID manually.",
        'login_required' => 'Please log in to finish linking your Telegram chat.',
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
    'signup' => [
        'instructions' => 'Ready to join? Tap the button below to open the sign-up page with your Telegram chat prefilled. If the link does not open, go to :url and enter your chat ID manually.',
        'welcome' => '🎉 Your account is linked to Telegram. You will now receive reminders directly in this chat.',
    ],
    'languages' => [
        'en' => 'English',
        'fr' => 'French',
    ],
    'buttons' => [
        'link_account' => 'Link my account',
        'language' => 'Choose language',
        'open_settings' => 'Open settings',
        'support' => 'Support',
        'link_open' => 'Link my account',
        'signup' => 'Create my account',
    ],
];
