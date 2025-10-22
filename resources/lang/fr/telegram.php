<?php

return [
    'generic' => [
        'friend' => 'maker',
    ],
    'start' => [
        'greeting' => '👋 Salut :name !',
        'intro' => 'Je suis le bot compagnon de 100Days AI Coach.',
        'chat_id' => 'Ton identifiant de chat Telegram est <code>:chat_id</code>.',
        'instructions' => 'Appuie sur le bouton ci-dessous pour relier automatiquement ton compte. Tu peux aussi coller l’identifiant dans Paramètres → Notifications si tu préfères.',
        'help' => 'Tape /help pour voir tout ce que je peux faire.',
    ],
    'help' => [
        'title' => '🤖 Commandes disponibles',
        'lines' => [
            '/start – afficher le message d’accueil',
            '/help – lister les commandes disponibles',
            '/signup – ouvrir la page d’inscription pré-remplie',
            '/language en|fr – choisir la langue utilisée pour les notifications',
            '/support – ouvrir la page support',
            '/stop – suspendre les notifications Telegram',
        ],
    ],
    'language' => [
        'unsupported' => '❌ Je comprends uniquement le français (fr) ou l’anglais (en). Essaie /language en ou /language fr.',
        'updated' => '✅ Langue mise à jour : :language.',
        'settings_hint' => 'Tu peux changer à tout moment avec /language en ou /language fr.',
        'pick' => 'Choisis ta langue préférée :',
    ],
    'link' => [
        'generated' => 'Appuie sur le bouton ci-dessous pour relier ton chat Telegram. Si le lien ne s’ouvre pas, rends-toi sur :url et saisis ton identifiant manuellement.',
        'login_required' => 'Connecte-toi pour terminer la liaison de ton chat Telegram.',
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
    'signup' => [
        'instructions' => 'Prêt à rejoindre la communauté ? Appuie sur le bouton ci-dessous pour ouvrir la page d’inscription avec ton chat Telegram pré-rempli. Si le lien ne s’ouvre pas, rends-toi sur :url et saisis ton identifiant manuellement.',
        'welcome' => '🎉 Ton compte est relié à Telegram. Tu recevras désormais les rappels directement dans cette conversation.',
    ],
    'languages' => [
        'en' => 'Anglais',
        'fr' => 'Français',
    ],
    'buttons' => [
        'link_account' => 'Relier mon compte',
        'language' => 'Choisir la langue',
        'open_settings' => 'Ouvrir les paramètres',
        'support' => 'Support',
        'link_open' => 'Relier mon compte',
        'signup' => 'Créer mon compte',
    ],
];
