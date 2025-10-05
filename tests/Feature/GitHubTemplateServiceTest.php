<?php

use App\Models\User;
use App\Services\GitHub\GitHubTemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('lists personal and organisation owners for the authenticated user', function (): void {
    $user = User::factory()->create();
    $profile = $user->profile()->create([
        'preferences' => $user->profilePreferencesDefaults(),
        'github_username' => 'jiordiviera',
        'github_access_token' => 'token-123',
    ]);

    Http::fake([
        'https://api.github.com/user/orgs' => Http::response([
            ['login' => 'my-org'],
            ['login' => 'side-project'],
        ]),
    ]);

    $owners = app(GitHubTemplateService::class)->listInstallableOwners($user->fresh('profile'));

    expect($owners)->toHaveCount(3)
        ->and($owners[0]['login'])->toBe('jiordiviera')
        ->and($owners[1]['login'])->toBe('my-org');
});

it('provisions a repository from the template and stores it', function (): void {
    config()->set('services.github.template.owner', 'jiordiviera');
    config()->set('services.github.template.repository', '100DaysOfCode-Template');

    $user = User::factory()->create();
    $profile = $user->profile()->create([
        'preferences' => $user->profilePreferencesDefaults(),
        'github_username' => 'jiordiviera',
        'github_access_token' => 'token-123',
    ]);

    Http::fake([
        'https://api.github.com/repos/jiordiviera/100DaysOfCode-Template/generate' => Http::response([
            'html_url' => 'https://github.com/jiordiviera/100days-of-code-journey',
            'id' => 42,
            'node_id' => 'R_abc',
            'full_name' => 'jiordiviera/100days-of-code-journey',
            'default_branch' => 'main',
        ], 201),
    ]);

    $service = app(GitHubTemplateService::class);

    $record = $service->provision($user->fresh(['profile']), '100days-of-code-journey', 'public', null);

    expect($record->repo_url)->toBe('https://github.com/jiordiviera/100days-of-code-journey')
        ->and($record->visibility)->toBe('public')
        ->and($user->repositories()->where('provider', 'github')->count())->toBe(1);
});

it('raises a descriptive error when the template is not accessible', function (): void {
    config()->set('services.github.template.owner', 'jiordiviera');
    config()->set('services.github.template.repository', '100DaysOfCode-Template');

    $user = User::factory()->create();
    $user->profile()->create([
        'preferences' => $user->profilePreferencesDefaults(),
        'github_username' => 'jiordiviera',
        'github_access_token' => 'token-123',
    ]);

    Http::fake([
        'https://api.github.com/repos/jiordiviera/100DaysOfCode-Template/generate' => Http::response([
            'message' => 'Not Found',
        ], 404),
    ]);

    $service = app(GitHubTemplateService::class);

    $this->expectExceptionMessage('GitHub n’a pas trouvé le template');

    $service->provision($user->fresh('profile'), 'my-repo');
});
