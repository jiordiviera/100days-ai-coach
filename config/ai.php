<?php

return [
    // Primary driver used by the AiManager. Set via AI_PROVIDER (fake|groq|openai|local) in .env.
    'default' => env('AI_PROVIDER', 'fake'),

    // Optional ordered list of fallback drivers (comma separated in env).
    'fallback' => array_filter([
        env('AI_FALLBACK_PROVIDER'),
    ]),

    // Driver map resolved by the IoC container.
    'drivers' => [
        'fake' => App\Services\Ai\Drivers\FakeAiDriver::class,
        'groq' => App\Services\Ai\Drivers\GroqAiDriver::class,
        'openai' => App\Services\Ai\Drivers\OpenAiAiDriver::class,
        'local' => App\Services\Ai\Drivers\LocalAiDriver::class,
    ],
];
