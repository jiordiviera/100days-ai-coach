<?php

return [
    'heading' => 'Mentions légales',
    'last_updated' => 'Dernière mise à jour : :date',
    'intro' => 'Ce projet open source est actuellement développé à titre expérimental. Les informations ci-dessous doivent être complétées avant toute mise en production publique ou exploitation commerciale.',
    'warning_title' => 'Informations à compléter',
    'warning_body' => 'Renseignez les variables d’environnement <code class="rounded bg-amber-200/60 px-1">LEGAL_*</code> pour publier les coordonnées officielles de l’éditeur et de l’hébergeur. Sans ces informations, cette page ne satisfait pas les obligations légales camerounaises et internationales.',

    'editor' => [
        'title' => '1. Éditeur du service',
        'subtitle' => 'Le site/app :app est édité par :',
        'fields' => [
            'name' => 'Nom / structure',
            'address' => 'Adresse',
            'contact' => 'Contact',
            'identification' => 'Identification',
            'publication_director' => 'Directeur·rice de publication',
        ],
        'contact_fallback' => 'Ouvrez un ticket sur :link',
        'identification_hint' => 'À compléter si applicable (immatriculation, RCCM, etc.)',
    ],

    'hosting' => [
        'title' => '2. Hébergement',
        'subtitle' => 'Le service est hébergé par :',
        'missing' => 'Aucune information d’hébergement n’est configurée. Complétez les variables <code class="rounded bg-muted px-1">LEGAL_HOST_*</code> lorsque l’application est déployée sur une infrastructure stable.',
        'local_note' => 'Pendant la phase de développement, le service peut être exécuté localement par les contributeur·ices. Les données restent alors sur les environnements personnels des mainteneurs.',
    ],

    'ip' => [
        'title' => '3. Propriété intellectuelle',
        'body' => 'Sauf mention contraire, le code source du projet est publié sous licence MIT. Le contenu généré par les utilisateurs (journaux, tâches, commentaires) reste leur propriété. Toute réutilisation doit respecter la licence et citer la source.',
    ],

    'data' => [
        'title' => '4. Données personnelles',
        'body' => 'Les informations sur la collecte et le traitement des données sont détaillées dans la :link. Conformément au RGPD et aux lois camerounaises en vigueur, vous disposez d’un droit d’accès, de rectification, d’effacement ainsi que d’un droit à la portabilité et à l’opposition.',
    ],

    'law' => [
        'title' => '5. Droit applicable',
        'body' => 'En l’absence d’entité commerciale dédiée, ce projet est administré depuis le Cameroun par son mainteneur principal. Tout différend éventuel sera soumis au droit camerounais. Avant toute action, merci de privilégier un contact amiable via GitHub ou l’adresse électronique renseignée ci-dessus.',
    ],
];
