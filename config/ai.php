<?php

return [
    'default' => env('AI_PROVIDER', 'fake'),
    'fallback' => array_filter([
        env('AI_FALLBACK_PROVIDER'),
    ]),
    'drivers' => [
        'fake' => App\Services\Ai\Drivers\FakeAiDriver::class,
        'groq' => App\Services\Ai\Drivers\GroqAiDriver::class,
        'openai' => App\Services\Ai\Drivers\OpenAiAiDriver::class,
    ],
];
