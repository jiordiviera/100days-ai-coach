<?php

use App\Jobs\CreateSupportTicketGitHubIssue;
use App\Models\SupportTicket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('creates a GitHub issue and updates the support ticket', function (): void {
    config()->set('services.github.support', [
        'owner' => 'test-owner',
        'repository' => 'support-repo',
        'token' => 'test-token',
        'default_labels' => ['support', 'feedback'],
    ]);

    Http::fake([
        'https://api.github.com/repos/test-owner/support-repo/issues' => Http::response([
            'html_url' => 'https://github.com/test-owner/support-repo/issues/42',
        ], 201),
    ]);

    $ticket = SupportTicket::factory()->create([
        'category' => 'bug',
        'message' => 'Impossible de créer un log.',
    ]);

    CreateSupportTicketGitHubIssue::dispatchSync($ticket);

    $ticket->refresh();

    expect($ticket->github_issue_url)->toBe('https://github.com/test-owner/support-repo/issues/42')
        ->and($ticket->status)->toBe('in_progress');
});

it('fails when the support repository is not configured', function (): void {
    config()->set('services.github.support', [
        'owner' => null,
        'repository' => null,
        'token' => null,
        'default_labels' => [],
    ]);

    $ticket = SupportTicket::factory()->create();

    expect(fn () => CreateSupportTicketGitHubIssue::dispatchSync($ticket))
        ->toThrow(\RuntimeException::class, 'GitHub support repository is not configured.');
});
