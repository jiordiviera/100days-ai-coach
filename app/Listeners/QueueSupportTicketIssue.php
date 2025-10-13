<?php

namespace App\Listeners;

use App\Events\SupportTicketCreated;
use App\Jobs\CreateSupportTicketGitHubIssue;
use Illuminate\Support\Arr;

class QueueSupportTicketIssue
{
    public function handle(SupportTicketCreated $event): void
    {
        $ticket = $event->ticket;

        if ($ticket->github_issue_url) {
            return;
        }

        $config = config('services.github.support', []);

        $token = Arr::get($config, 'token');
        $owner = Arr::get($config, 'owner');
        $repository = Arr::get($config, 'repository');

        if (! $token || ! $owner || ! $repository) {
            return;
        }

        $categories = collect(Arr::wrap(config('support.auto_issue_categories', [])))
            ->filter()
            ->map(fn ($category) => strtolower(trim((string) $category)))
            ->all();

        if (empty($categories)) {
            return;
        }

        if (! $ticket->category || ! in_array(strtolower((string) $ticket->category), $categories, true)) {
            return;
        }

        CreateSupportTicketGitHubIssue::dispatch($ticket);
    }
}
