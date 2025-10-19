<?php

return [
    'faq' => [
        'sections' => [
            'getting_started' => [
                'title' => 'Getting started',
                'items' => [
                    'start_run' => [
                        'question' => 'How do I launch my first #100DaysOfCode run?',
                        'answer' => 'Sign up, follow the onboarding, and the app will create your first project, your day 0 log, and schedule daily reminders.',
                    ],
                    'privacy' => [
                        'question' => 'Can I use the app without sharing publicly?',
                        'answer' => 'Yes. Everything is private by default. You decide later which logs or projects to make public.',
                    ],
                ],
            ],
            'daily_logs_ai' => [
                'title' => 'Daily logs & AI',
                'items' => [
                    'summaries' => [
                        'question' => 'How does the AI generate summaries?',
                        'answer' => 'Each log can trigger an AI summary (Groq by default). You can regenerate once per day if you need an updated version.',
                    ],
                    'data_usage' => [
                        'question' => 'Is my data reused?',
                        'answer' => 'Your content stays inside your workspace. AI requests only go to the provider you choose and are not used to retrain models.',
                    ],
                ],
            ],
            'support_notifications' => [
                'title' => 'Support & notifications',
                'items' => [
                    'report' => [
                        'question' => 'How do I report a bug or idea?',
                        'answer' => 'Use the landing page form or this support page to send feedback. We reply by email and create a GitHub issue when needed.',
                    ],
                    'follow_up' => [
                        'question' => 'Can I track the progress of my ticket?',
                        'answer' => 'You receive an email when we pick up a ticket. If it becomes public we send you the GitHub link.',
                    ],
                ],
            ],
        ],
    ],
    'resources' => [
        'daily_log_guide' => [
            'title' => 'Daily Log guide',
            'description' => 'Structure each entry, choose optional fields, and manage what you share publicly.',
        ],
        'onboarding_checklist' => [
            'title' => 'Onboarding checklist',
            'description' => 'Set up projects, reminders, and shared templates step by step.',
        ],
        'public_roadmap' => [
            'title' => 'Public roadmap',
            'description' => 'See the features in progress, the backlog, and vote on upcoming priorities.',
        ],
    ],
];
