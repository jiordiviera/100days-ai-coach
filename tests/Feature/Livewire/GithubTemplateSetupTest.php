<?php

use App\Livewire\Partials\GithubTemplateSetup;
use App\Models\User;
use App\Models\UserRepository;
use App\Services\GitHub\GitHubTemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

uses(RefreshDatabase::class);

afterEach(function (): void {
    \Mockery::close();
});

it('displays the existing repository link when already provisioned', function (): void {
    $user = User::factory()->create();
    $profile = $user->profile()->create([
        'preferences' => $user->profilePreferencesDefaults(),
        'github_access_token' => 'token-123',
        'github_username' => 'jiordiviera',
    ]);

    $repository = $user->repositories()->create([
        'provider' => 'github',
        'repo_owner' => 'jiordiviera',
        'repo_name' => '100days-of-code',
        'repo_url' => 'https://github.com/jiordiviera/100days-of-code',
        'visibility' => 'public',
        'status' => 'created',
    ]);

    Cache::flush();

    Http::fake([
        'https://api.github.com/user/orgs' => Http::response([], 200),
    ]);

    $this->actingAs($user);

    Livewire::test(GithubTemplateSetup::class)
        ->assertSee('Repository #100DaysOfCode')
        ->assertSee('jiordiviera/100days-of-code');
});

it('calls the template service to create the repository', function (): void {
    $user = User::factory()->create();
    $profile = $user->profile()->create([
        'preferences' => $user->profilePreferencesDefaults(),
        'github_access_token' => 'token-123',
        'github_username' => 'jiordiviera',
    ]);

    Http::fake([
        'https://api.github.com/user/orgs' => Http::response([], 200),
    ]);

    $mock = \Mockery::mock(GitHubTemplateService::class);
    $this->instance(GitHubTemplateService::class, $mock);

    $mock->shouldReceive('listInstallableOwners')
        ->andReturn([
            ['login' => 'jiordiviera', 'display' => 'jiordiviera'],
        ]);

    $mock->shouldReceive('provision')
        ->once()
        ->andReturn(UserRepository::make([
            'provider' => 'github',
            'repo_owner' => 'jiordiviera',
            'repo_name' => '100days-of-code',
            'repo_url' => 'https://github.com/jiordiviera/100days-of-code',
            'visibility' => 'private',
            'status' => 'created',
        ]));

    $this->actingAs($user);

    Cache::flush();

    Livewire::test(GithubTemplateSetup::class)
        ->call('loadOwners')
        ->set('githubForm.repo_name', '100days-of-code')
        ->call('createRepository')
        ->assertSet('repository.repo_name', '100days-of-code');
});
