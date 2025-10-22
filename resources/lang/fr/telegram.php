<?php

return [
    'generic' => [
        'friend' => 'maker',
    ],
    'start' => [
        'greeting' => '👋 Salut :name !',
        'intro' => 'Je suis le bot compagnon de 100Days AI Coach.',
        'chat_id' => 'Ton identifiant de chat Telegram est <code>:chat_id</code>.',
        'instructions' => 'Copie cet identifiant dans l’app (Paramètres → Notifications) pour recevoir les rappels ici.',
        'help' => 'Tape /help pour voir tout ce que je peux faire.',
    ],
    'help' => [
        'title' => '🤖 Commandes disponibles',
        'lines' => [
            '/start – afficher le message d’accueil',
            '/help – lister les commandes disponibles',
            '/language en|fr – choisir la langue utilisée pour les notifications',
            '/support – ouvrir la page support',
            '/stop – suspendre les notifications Telegram',
        ],
    ],
    'language' => [
        'unsupported' => '❌ Je comprends uniquement le français (fr) ou l’anglais (en). Essaie /language en ou /language fr.',
        'updated' => '✅ Langue mise à jour : :language.',
        'settings_hint' => 'Tu peux changer à tout moment avec /language en ou /language fr.',
    ],
    'support' => [
        'message' => '📮 Besoin d’aide ? La page support est ici : :url',
    ],
    'stop' => [
        'message' => "🚫 Notifications Telegram mises en pause.\nTu peux les réactiver depuis les paramètres de l’app quand tu veux.",
    ],
    'fallback' => [
        'unknown_command' => 'Cette commande est inconnue. Tape /help pour voir ce que je peux faire.',
        'default' => "Merci ! Si tu as besoin d’aide, utilise /support ou le formulaire support dans l’app.",
    ],
    'languages' => [
        'en' => 'Anglais',
        'fr' => 'Français',
    ],
];
