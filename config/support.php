<?php

return [
    'team_recipients' => array_filter([
        env('SUPPORT_TEAM_EMAIL', 'hello@jiordiviera.me'),
        env('SUPPORT_TEAM_EMAIL_SECONDARY', 'jiordikengne@gmail.com'),
    ]),
    'team_telegram_chat_ids' => collect([
        env('SUPPORT_TEAM_TELEGRAM_CHAT_ID'),
        ...explode(',', (string) env('SUPPORT_TEAM_TELEGRAM_IDS', '')),
    ])
        ->map(fn ($value) => is_string($value) ? trim($value) : $value)
        ->filter()
        ->unique()
        ->values()
        ->all(),
    'auto_issue_categories' => ['bug'],
    'faq_sections' => [
        [
            'title' => 'support.faq.sections.getting_started.title',
            'items' => [
                [
                    'question' => 'support.faq.sections.getting_started.items.start_run.question',
                    'answer' => 'support.faq.sections.getting_started.items.start_run.answer',
                ],
                [
                    'question' => 'support.faq.sections.getting_started.items.privacy.question',
                    'answer' => 'support.faq.sections.getting_started.items.privacy.answer',
                ],
            ],
        ],
        [
            'title' => 'support.faq.sections.daily_logs_ai.title',
            'items' => [
                [
                    'question' => 'support.faq.sections.daily_logs_ai.items.summaries.question',
                    'answer' => 'support.faq.sections.daily_logs_ai.items.summaries.answer',
                ],
                [
                    'question' => 'support.faq.sections.daily_logs_ai.items.data_usage.question',
                    'answer' => 'support.faq.sections.daily_logs_ai.items.data_usage.answer',
                ],
            ],
        ],
        [
            'title' => 'support.faq.sections.support_notifications.title',
            'items' => [
                [
                    'question' => 'support.faq.sections.support_notifications.items.report.question',
                    'answer' => 'support.faq.sections.support_notifications.items.report.answer',
                ],
                [
                    'question' => 'support.faq.sections.support_notifications.items.follow_up.question',
                    'answer' => 'support.faq.sections.support_notifications.items.follow_up.answer',
                ],
            ],
        ],
    ],
    'resources' => [
        // [
        //     'title' => 'support.resources.daily_log_guide.title',
        //     'description' => 'support.resources.daily_log_guide.description',
        //     'url' => 'https://www.notion.so/100days-ai-coach/daily-log-guide',
        // ],
        // [
        //     'title' => 'support.resources.onboarding_checklist.title',
        //     'description' => 'support.resources.onboarding_checklist.description',
        //     'url' => 'https://www.notion.so/100days-ai-coach/onboarding-checklist',
        // ],
        [
            'title' => 'support.resources.public_roadmap.title',
            'description' => 'support.resources.public_roadmap.description',
            'url' => 'https://github.com/jiordiviera/100days-ai-coach/projects',
        ],
    ],
];
