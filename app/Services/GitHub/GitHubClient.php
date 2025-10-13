<?php

namespace App\Services\GitHub;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GitHubClient
{
    public function __construct(
        private readonly ?string $baseUri = null,
    ) {}

    public function listOrganizations(string $accessToken): array
    {
        $response = $this->request($accessToken)
            ->get('user/orgs');

        return $response->json() ?? [];
    }

    public function createRepositoryFromTemplate(string $accessToken, string $templateOwner, string $templateRepository, array $payload): array
    {
        $response = $this->request($accessToken)
            ->withHeaders([
                'Accept' => 'application/vnd.github+json',
            ])
            ->post("repos/{$templateOwner}/{$templateRepository}/generate", $payload);

        return $response->json() ?? [];
    }

    public function createIssue(string $accessToken, string $owner, string $repository, array $payload): array
    {
        $response = $this->request($accessToken)
            ->withHeaders([
                'Accept' => 'application/vnd.github+json',
            ])
            ->post("repos/{$owner}/{$repository}/issues", $payload);

        return $response->json() ?? [];
    }

    protected function request(string $accessToken): PendingRequest
    {
        $baseUri = rtrim($this->baseUri ?? config('services.github.base_uri', 'https://api.github.com/'), '/').'/';

        $request = Http::baseUrl($baseUri)
            ->withToken($accessToken)
            ->acceptJson()
            ->withHeaders([
                'X-GitHub-Api-Version' => '2022-11-28',
            ])
            ->asJson()
            ->timeout(10)
            ->retry(2, 200, fn ($exception) => $exception instanceof ConnectionException);

        return $request->throw(fn ($response, RequestException $exception) => $this->handleException($response->json() ?? [], $exception));
    }

    protected function messageFromResponse(?int $status, array $body): ?string
    {
        $errors = Arr::wrap(Arr::get($body, 'errors', []));

        if ($status === 401) {
            return 'GitHub authentication failed. Please reconnect your account.';
        }

        if ($status === 403) {
            $message = Arr::get($body, 'message');

            return $message ?: 'GitHub denied the request. Check repository permissions or rate limits.';
        }

        if ($status === 404) {
            $template = config('services.github.template');
            $owner = $template['owner'] ?? 'template-owner';
            $repo = $template['repository'] ?? 'template-repo';

            return sprintf(
                'GitHub n’a pas trouvé le template (%s/%s) ou votre token manque le scope repo. Vérifiez que le dépôt est marqué comme “Template” et reconnectez votre compte GitHub avec les bonnes autorisations.',
                $owner,
                $repo,
            );
        }

        if ($status === 422) {
            if ($errors) {
                $first = $errors[0];

                if (is_array($first)) {
                    return $first['message'] ?? $first['code'] ?? 'GitHub rejected the repository creation.';
                }

                if (is_string($first)) {
                    return $first;
                }
            }

            return Arr::get($body, 'message');
        }

        return Arr::get($body, 'message');
    }

    protected function handleException(array $body, RequestException $exception): void
    {
        $status = $exception->response?->status();
        Log::error('Exception', [$exception]);
        $message = $this->messageFromResponse($status, $body) ?? $exception->getMessage();

        throw new GitHubApiException($message, $status, $body);
    }
}
