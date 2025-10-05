<?php

namespace App\Services\GitHub;

use RuntimeException;

class GitHubApiException extends RuntimeException
{
    public function __construct(string $message, public readonly ?int $status = null, public readonly array $context = [])
    {
        parent::__construct($message, $status ?? 0);
    }
}
