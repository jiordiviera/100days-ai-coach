<?php

namespace App\Services\GitHub;

use App\Models\User;
use App\Models\UserRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class GitHubTemplateService
{
    public function __construct(
        private readonly GitHubClient $client,
    ) {}

    public function listInstallableOwners(User $user): array
    {
        $token = $this->accessToken($user);

        $owners = [];
        $profile = $user->profile;

        if ($profile?->github_username) {
            $owners[] = [
                'type' => 'user',
                'login' => $profile->github_username,
                'display' => $profile->github_username.' (personnel)',
            ];
        }

        $orgs = $this->client->listOrganizations($token);
        foreach ($orgs as $org) {
            $owners[] = [
                'type' => 'org',
                'login' => $org['login'],
                'display' => $org['login'].' (organisation)',
            ];
        }

        return $owners;
    }

    public function provision(User $user, string $repoName, string $visibility = 'private', ?string $owner = null): UserRepository
    {
        $token = $this->accessToken($user);
        $owner = $owner ?: $this->defaultOwner($user);

        $repoName = Str::of($repoName)->trim()->replace(' ', '-')->slug('-');
        $visibility = strtolower($visibility) === 'public' ? 'public' : 'private';

        $config = config('services.github.template');
        $templateOwner = $config['owner'] ?? null;
        $templateRepository = $config['repository'] ?? null;

        if (! $templateOwner || ! $templateRepository) {
            throw new GitHubApiException('GitHub template repository is not configured.');
        }

        $payload = [
            'owner' => $owner,
            'name' => $repoName,
            'private' => $visibility !== 'public',
        ];

        $response = $this->client->createRepositoryFromTemplate($token, $templateOwner, $templateRepository, $payload);

        $repoUrl = Arr::get($response, 'html_url');
        $meta = Arr::only($response, ['id', 'node_id', 'full_name', 'default_branch']);
        $meta['source'] = [
            'template_owner' => $templateOwner,
            'template_repository' => $templateRepository,
        ];

        /** @var UserRepository $record */
        $record = $user->repositories()->firstOrNew([
            'provider' => 'github',
        ]);

        $record->fill([
            'repo_owner' => $owner,
            'repo_name' => $repoName,
            'repo_url' => $repoUrl,
            'visibility' => $visibility,
            'status' => 'created',
            'meta' => $meta,
        ])->save();

        return $record;
    }

    protected function accessToken(User $user): string
    {
        $token = $user->profile?->github_access_token;

        if (! $token) {
            throw new GitHubApiException('GitHub access token manquant. Reconnectez votre compte GitHub.');
        }

        return $token;
    }

    protected function defaultOwner(User $user): string
    {
        $profile = $user->profile;

        if ($profile?->github_username) {
            return $profile->github_username;
        }

        return Str::slug($user->name ?: 'my-repo');
    }
}
