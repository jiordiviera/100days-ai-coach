<?php

return [
    'templates' => [
        [
            'id' => 'starter-webapp',
            'name' => 'Starter Web App',
            'description' => 'Structure de base pour une application web (setup, UI, backend).',
            'tasks' => [
                'Initialiser le dépôt et le README',
                'Configurer l’authentification',
                'Mettre en place la navigation principale',
                'Déployer une version de préproduction',
            ],
        ],
        [
            'id' => 'learning-sprint',
            'name' => 'Sprint d’apprentissage',
            'description' => 'Idéal pour documenter un module de formation ou une série de cours.',
            'tasks' => [
                'Lister les ressources à étudier',
                'Suivre la première session',
                'Prendre des notes synthétiques',
                'Planifier une mise en pratique',
            ],
        ],
        [
            'id' => 'refactor-run',
            'name' => 'Sprint de refactorisation',
            'description' => 'Checklist pour remettre à plat un projet existant.',
            'tasks' => [
                'Cartographier les points techniques à revoir',
                'Mettre en place les tests manquants',
                'Refactoriser un module prioritaire',
                'Documenter les changements et prochains chantiers',
            ],
        ],
    ],
];

