<?php

namespace App\Jobs;

use App\Models\SupportTicket;
use App\Services\GitHub\SupportTicketIssueService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateSupportTicketGitHubIssue implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public SupportTicket $ticket) {}

    public function handle(SupportTicketIssueService $service): void
    {
        $response = $service->createIssue($this->ticket);

        $this->ticket->forceFill([
            'github_issue_url' => $response['html_url'] ?? null,
            'status' => 'in_progress',
        ])->save();
    }
}
