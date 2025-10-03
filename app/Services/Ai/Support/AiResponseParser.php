<?php

namespace App\Services\Ai\Support;

use Illuminate\Support\Arr;
use RuntimeException;

class AiResponseParser
{
    public static function extractStructuredPayload(array $response): array
    {
        $content = trim((string) Arr::get($response, 'choices.0.message.content'));

        if ($content === '') {
            throw new RuntimeException('Empty AI response content.');
        }

        $json = self::stripFence($content);

        $decoded = json_decode($json, true);

        if (! is_array($decoded)) {
            throw new RuntimeException('Invalid AI response JSON: '.$json);
        }

        return $decoded;
    }

    protected static function stripFence(string $content): string
    {
        $content = trim($content);

        if (str_starts_with($content, '```')) {
            $content = preg_replace('/^```[a-zA-Z0-9]*\s*/', '', $content);
            $content = preg_replace('/```$/', '', $content);
        }

        return trim($content);
    }
}
