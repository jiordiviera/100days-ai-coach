<?php

return [
    'team_recipients' => array_filter([
        env('SUPPORT_TEAM_EMAIL', 'hello@jiordiviera.me'),
        env('SUPPORT_TEAM_EMAIL_SECONDARY', 'jiordikengne@gmail.com'),
    ]),
    'auto_issue_categories' => ['bug'],
    'faq_sections' => [
        [
            'title' => 'Bien démarrer',
            'items' => [
                [
                    'question' => 'Comment lancer mon premier run #100DaysOfCode ?',
                    'answer' => "Inscris-toi, puis suis l'onboarding : l'app crée ton premier project, ton log de jour 0 et planifie les rappels quotidiens.",
                ],
                [
                    'question' => 'Puis-je utiliser l’app sans partager publiquement ?',
                    'answer' => 'Oui. Tout est privé par défaut. Choisis ensuite quels logs ou projets tu rends publics.',
                ],
            ],
        ],
        [
            'title' => 'Daily logs & IA',
            'items' => [
                [
                    'question' => 'Comment l’IA génère les résumés ?',
                    'answer' => 'Chaque log peut déclencher une génération IA (Groq par défaut). Tu peux re-générer manuellement une fois par jour.',
                ],
                [
                    'question' => 'Mes données sont-elles ré-utilisées ?',
                    'answer' => "Les contenus restent dans ton workspace. Les requêtes IA transitent uniquement chez le provider choisi et ne sont pas ré-entraînées.",
                ],
            ],
        ],
        [
            'title' => 'Support & notifications',
            'items' => [
                [
                    'question' => 'Comment signaler un bug ou une idée ?',
                    'answer' => 'Le formulaire de la landing page ou la page support permet de laisser un feedback. On répond par email et on crée une issue GitHub si besoin.',
                ],
                [
                    'question' => 'Puis-je suivre l’avancement de mon ticket ?',
                    'answer' => "Tu reçois un email lorsqu’un ticket est pris en charge. S’il devient public, on t’envoie le lien GitHub.",
                ],
            ],
        ],
    ],
    'resources' => [
        [
            'title' => 'Guide Daily Log',
            'description' => 'Structurer chaque entrée, choisir les champs facultatifs et partager en public.',
            'url' => 'https://www.notion.so/100days-ai-coach/daily-log-guide',
        ],
        [
            'title' => 'Check-list Onboarding',
            'description' => 'Les étapes pour mettre en place projets, rappels et templates partagés.',
            'url' => 'https://www.notion.so/100days-ai-coach/onboarding-checklist',
        ],
        [
            'title' => 'Roadmap publique',
            'description' => 'Consulter les features en cours, le backlog et voter pour les prochaines priorités.',
            'url' => 'https://github.com/jiordiviera/100days-ai-coach/projects',
        ],
    ],
];
