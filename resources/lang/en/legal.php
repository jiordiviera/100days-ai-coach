<?php

return [
    'heading' => 'Legal Notice',
    'last_updated' => 'Last updated: :date',
    'intro' => 'This open-source project is currently developed on an experimental basis. The information below must be completed before any public production release or commercial use.',
    'warning_title' => 'Information pending',
    'warning_body' => 'Fill in the <code class="rounded bg-amber-200/60 px-1">LEGAL_*</code> environment variables to publish the official details of the editor and hosting provider. Without them, this page does not meet Cameroonian and international legal requirements.',

    'editor' => [
        'title' => '1. Publisher',
        'subtitle' => 'The :app website/app is published by:',
        'fields' => [
            'name' => 'Name / organisation',
            'address' => 'Address',
            'contact' => 'Contact',
            'identification' => 'Registration',
            'publication_director' => 'Publishing director',
        ],
        'contact_fallback' => 'Open a ticket on :link',
        'identification_hint' => 'Fill in if applicable (registration, business ID, etc.)',
    ],

    'hosting' => [
        'title' => '2. Hosting',
        'subtitle' => 'The service is hosted by:',
        'missing' => 'No hosting information is configured. Complete the <code class="rounded bg-muted px-1">LEGAL_HOST_*</code> variables when deploying to a stable infrastructure.',
        'local_note' => 'During development, the service can run locally. Data therefore remains on the maintainers’ personal environments.',
    ],

    'ip' => [
        'title' => '3. Intellectual property',
        'body' => 'Unless otherwise stated, the project’s source code is published under the MIT licence. User-generated content (logs, tasks, comments) remains their property. Any reuse must respect the licence and credit the source.',
    ],

    'data' => [
        'title' => '4. Personal data',
        'body' => 'Details about data collection and processing can be found in the :link. In accordance with the GDPR and Cameroonian law, you have rights of access, rectification, erasure, portability and objection.',
    ],

    'law' => [
        'title' => '5. Governing law',
        'body' => 'In the absence of a dedicated company, this project is administered from Cameroon by its maintainer. Any dispute will fall under Cameroonian law. Before taking any action, please contact us via GitHub or the e-mail address above.',
    ],
];
