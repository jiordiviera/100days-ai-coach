<?php

return [
    'heading' => 'Politique de confidentialité',
    'tagline' => 'Protection des données & usage responsable',
    'last_updated' => 'Dernière mise à jour : :date',
    'intro' => 'Cette politique décrit la manière dont :app traite les données personnelles collectées dans le cadre du défi #100DaysOfCode. Toutes les informations ci-dessous reflètent l’état actuel du projet open source et doivent être révisées avant tout déploiement public.',

    'controller' => [
        'title' => '1. Responsable du traitement',
        'body' => 'Le traitement des données est assuré par le mainteneur du projet open source. Pour toute question ou demande liée à vos données personnelles, contactez :link.',
        'note' => 'Aucune structure commerciale dédiée n’existe à ce stade. Les contributeur·ices qui déploient le projet doivent adapter cette section en fonction de leur statut légal.',
    ],

    'collected' => [
        'title' => '2. Données collectées',
        'items' => [
            'account' => 'Données de compte : nom, adresse e-mail, mot de passe (hashé), fuseau horaire, préférences d’interface.',
            'content' => 'Contenus saisis : journaux quotidiens, projets, tâches, commentaires, fichiers facultatifs associés.',
            'integrations' => 'Intégrations : jetons d’accès GitHub (chiffrés en base), identifiants d’organisation, état des dépôts clonés, clé API WakaTime (chiffrée) et statistiques d’activité importées.',
            'technical' => 'Données techniques : adresses IP, user-agent, journaux applicatifs (erreurs, temps de réponse) conservés à des fins de diagnostic.',
        ],
    ],

    'purposes' => [
        'title' => '3. Finalités du traitement',
        'items' => [
            'core' => 'Fournir les fonctionnalités principales (journal quotidien, suivi de streak, gestion de projets/tâches).',
            'ai' => 'Générer des contenus assistés par IA (résumé, tags, coach tip, brouillons sociaux).',
            'sync' => 'Synchroniser l’activité réelle de codage (WakaTime) et proposer des insights personnalisés.',
            'maintenance' => 'Assurer la maintenance, la sécurité et la détection d’anomalies.',
            'support' => 'Faciliter le support utilisateur via le formulaire de feedback ou GitHub Issues.',
        ],
    ],

    'sharing' => [
        'title' => '4. Partage et sous-traitance',
        'intro' => 'Les données ne sont ni vendues ni louées. Elles peuvent être transmises à des services tiers uniquement pour les besoins suivants :',
        'items' => [
            'ai' => 'Fournisseurs IA : prompts envoyés à Groq (Mixtral) et, en cas de repli, à OpenAI (GPT-4o-mini). Les textes de vos journaux peuvent être inclus dans ces requêtes afin de générer le résumé et les suggestions.',
            'wakatime' => 'WakaTime : récupération des statistiques d’activité à l’aide de votre clé API personnelle.',
            'hosting' => 'Infrastructure d’hébergement : stockage des bases de données, journaux et sauvegardes (à compléter selon votre déploiement).',
            'law' => 'Autorités ou obligations légales : uniquement en cas d’exigence réglementaire.',
        ],
        'warning' => 'Lors d’un déploiement public, complétez la liste précise des sous-traitants (hébergeur, CDN, outils d’analyse, etc.).',
    ],

    'retention' => [
        'title' => '5. Durée de conservation',
        'items' => [
            'account' => 'Données de compte : conservées tant que l’utilisateur dispose d’un accès. Suppression possible sur demande.',
            'logs' => 'Journaux, projets et tâches : conservés jusqu’à suppression manuelle par l’utilisateur ou fermeture du compte.',
            'tokens' => 'Clés API / jetons OAuth : stockés chiffrés et supprimés lors de la déconnexion du service concerné.',
            'tech' => 'Logs techniques : rotation automatique selon la configuration Laravel/Horizon (à adapter lors du déploiement).',
        ],
    ],

    'security' => [
        'title' => '6. Sécurité',
        'body' => 'L’application s’appuie sur les mécanismes de sécurité Laravel : hachage des mots de passe, chiffrement des champs sensibles (GitHub/WakaTime), protection CSRF, politique de permissions. Les contributions doivent veiller à ne pas exposer de secrets dans les journaux ou captures d’écran partagées.',
    ],

    'rights' => [
        'title' => '7. Vos droits',
        'body' => 'Vous pouvez demander l’accès, la rectification, la suppression ou la portabilité de vos données. Utilisez le canal de contact indiqué ci-dessus en précisant l’adresse e-mail associée à votre compte. Une preuve d’identité peut être demandée pour sécuriser la procédure.',
    ],

    'changes' => [
        'title' => '8. Évolutions de la politique',
        'body' => 'Cette politique peut être mise à jour afin de refléter les évolutions techniques ou réglementaires du projet. La date de mise à jour figurant en tête de page sera ajustée à chaque modification.',
    ],
];
