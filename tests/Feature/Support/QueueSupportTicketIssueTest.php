<?php

use App\Events\SupportTicketCreated;
use App\Jobs\CreateSupportTicketGitHubIssue;
use App\Models\SupportTicket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;

uses(RefreshDatabase::class);

it('queues a GitHub issue job when the ticket category is configured', function (): void {
    config()->set('support.auto_issue_categories', ['bug']);
    config()->set('services.github.support', [
        'owner' => 'test-owner',
        'repository' => 'support-repo',
        'token' => 'test-token',
        'default_labels' => [],
    ]);

    Bus::fake();

    $ticket = SupportTicket::factory()->create([
        'category' => 'bug',
    ]);

    SupportTicketCreated::dispatch($ticket);

    Bus::assertDispatched(CreateSupportTicketGitHubIssue::class, function (CreateSupportTicketGitHubIssue $job) use ($ticket) {
        return $job->ticket->is($ticket);
    });
});

it('does not queue a job when category is not in the allow list', function (): void {
    config()->set('support.auto_issue_categories', ['bug']);
    config()->set('services.github.support', [
        'owner' => 'test-owner',
        'repository' => 'support-repo',
        'token' => 'test-token',
        'default_labels' => [],
    ]);

    Bus::fake();

    $ticket = SupportTicket::factory()->create([
        'category' => 'idea',
    ]);

    SupportTicketCreated::dispatch($ticket);

    Bus::assertNotDispatched(CreateSupportTicketGitHubIssue::class);
});
