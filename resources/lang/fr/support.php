<?php

return [
    'faq' => [
        'sections' => [
            'getting_started' => [
                'title' => 'Bien démarrer',
                'items' => [
                    'start_run' => [
                        'question' => 'Comment lancer mon premier run #100DaysOfCode ?',
                        'answer' => "Inscris-toi, puis suis l'onboarding : l'app crée ton premier projet, ton log de jour 0 et planifie les rappels quotidiens.",
                    ],
                    'privacy' => [
                        'question' => 'Puis-je utiliser l’app sans partager publiquement ?',
                        'answer' => 'Oui. Tout est privé par défaut. Choisis ensuite quels logs ou projets tu rends publics.',
                    ],
                ],
            ],
            'daily_logs_ai' => [
                'title' => 'Daily logs & IA',
                'items' => [
                    'summaries' => [
                        'question' => 'Comment l’IA génère les résumés ?',
                        'answer' => 'Chaque log peut déclencher une génération IA (Groq par défaut). Tu peux re-générer manuellement une fois par jour.',
                    ],
                    'data_usage' => [
                        'question' => 'Mes données sont-elles ré-utilisées ?',
                        'answer' => 'Les contenus restent dans ton workspace. Les requêtes IA transitent uniquement chez le provider choisi et ne sont pas ré-entraînées.',
                    ],
                ],
            ],
            'support_notifications' => [
                'title' => 'Support & notifications',
                'items' => [
                    'report' => [
                        'question' => 'Comment signaler un bug ou une idée ?',
                        'answer' => 'Le formulaire de la landing page ou la page support permet de laisser un feedback. On répond par email et on crée une issue GitHub si besoin.',
                    ],
                    'follow_up' => [
                        'question' => 'Puis-je suivre l’avancement de mon ticket ?',
                        'answer' => 'Tu reçois un email lorsqu’un ticket est pris en charge. S’il devient public, on t’envoie le lien GitHub.',
                    ],
                ],
            ],
        ],
    ],
    'resources' => [
        'daily_log_guide' => [
            'title' => 'Guide Daily Log',
            'description' => 'Structurer chaque entrée, choisir les champs facultatifs et partager en public.',
        ],
        'onboarding_checklist' => [
            'title' => 'Check-list Onboarding',
            'description' => 'Les étapes pour mettre en place projets, rappels et templates partagés.',
        ],
        'public_roadmap' => [
            'title' => 'Roadmap publique',
            'description' => 'Consulter les features en cours, le backlog et voter pour les prochaines priorités.',
        ],
    ],
];
