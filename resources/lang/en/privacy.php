<?php

return [
    'heading' => 'Privacy policy',
    'tagline' => 'Data protection & responsible usage',
    'last_updated' => 'Last updated: :date',
    'intro' => 'This policy explains how :app handles personal data collected as part of the #100DaysOfCode challenge. The information below reflects the current state of the open-source project and must be reviewed before any public deployment.',

    'controller' => [
        'title' => '1. Data controller',
        'body' => 'Data processing is handled by the maintainer of the open-source project. For any question or request regarding your personal data, contact :link.',
        'note' => 'No dedicated company exists at this stage. Contributors deploying the project should adapt this section according to their legal status.',
    ],

    'collected' => [
        'title' => '2. Data we collect',
        'items' => [
            'account' => 'Account data: name, email address, password (hashed), timezone, interface preferences.',
            'content' => 'Content you create: daily logs, projects, tasks, comments, optional attachments.',
            'integrations' => 'Integrations: GitHub access tokens (encrypted at rest), organisation IDs, repository provisioning status, WakaTime API key (encrypted) and imported activity stats.',
            'technical' => 'Technical data: IP addresses, user-agent, application logs (errors, response times) kept for diagnostics.',
        ],
    ],

    'purposes' => [
        'title' => '3. How we use your data',
        'items' => [
            'core' => 'Provide the core features (daily log, streak tracking, project/task management).',
            'ai' => 'Generate AI-assisted content (summary, tags, coach tip, social drafts).',
            'sync' => 'Synchronise actual coding activity (WakaTime) and produce tailored insights.',
            'maintenance' => 'Maintain, secure, and troubleshoot the service.',
            'support' => 'Handle user support through the feedback form or GitHub Issues.',
        ],
    ],

    'sharing' => [
        'title' => '4. Data sharing & processors',
        'intro' => 'Data is neither sold nor rented. It can be shared with third parties only for the following purposes:',
        'items' => [
            'ai' => 'AI providers: prompts sent to Groq (Mixtral) and, as a fallback, to OpenAI (GPT-4o-mini). Log content may be included to generate summaries and tips.',
            'wakatime' => 'WakaTime: fetching activity statistics via your personal API key.',
            'hosting' => 'Hosting infrastructure: Hetzner Cloud (Germany) VPS managed with Ansible, running Ubuntu, Nginx, and PostgreSQL. Application data, logs, and encrypted backups are stored on this server.',
            'law' => 'Authorities or legal obligations: only when required by law.',
        ],
        'warning' => 'As of today, no CDN, analytics suite, or transactional email provider is used. Should we add one, this section will be updated with the new processor.',
    ],

    'retention' => [
        'title' => '5. Data retention',
        'items' => [
            'account' => 'Account data: kept while the user has access; deleted on request.',
            'logs' => 'Logs, projects, tasks: kept until manually deleted or the account is closed.',
            'tokens' => 'API keys / OAuth tokens: stored encrypted and revoked when the service is disconnected.',
            'tech' => 'Technical logs: stored on the Hetzner VPS and purged manually during maintenance windows (no automated rotation configured yet).',
        ],
    ],

    'security' => [
        'title' => '6. Security',
        'body' => 'The application relies on Laravel’s security features: password hashing, encrypted sensitive fields (GitHub/WakaTime), CSRF protection, and permission policies. Contributors should avoid exposing secrets in logs or shared screenshots.',
    ],

    'rights' => [
        'title' => '7. Your rights',
        'body' => 'You can request access, rectification, deletion or portability of your data. Contact us using the channel above, mentioning the email linked to your account. Proof of identity may be requested.',
    ],

    'changes' => [
        'title' => '8. Policy changes',
        'body' => 'This policy may be updated to reflect technical or regulatory developments. The update date shown above will change accordingly.',
    ],
];
