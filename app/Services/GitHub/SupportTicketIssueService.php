<?php

namespace App\Services\GitHub;

use App\Models\SupportTicket;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use RuntimeException;

class SupportTicketIssueService
{
    public function __construct(private readonly GitHubClient $client) {}

    public function createIssue(SupportTicket $ticket): array
    {
        $config = config('services.github.support', []);

        $token = Arr::get($config, 'token');
        $owner = Arr::get($config, 'owner');
        $repository = Arr::get($config, 'repository');
        $labels = Arr::get($config, 'default_labels', []);

        if (! $token || ! $owner || ! $repository) {
            throw new RuntimeException('GitHub support repository is not configured.');
        }

        $title = sprintf('[%s] %s', Str::upper((string) $ticket->category), Str::limit($ticket->name ?? 'Utilisateur', 48));

        $body = $this->buildIssueBody($ticket);

        $payload = [
            'title' => $title,
            'body' => $body,
        ];

        if (! empty($labels)) {
            $payload['labels'] = $labels;
        }

        return $this->client->createIssue($token, $owner, $repository, $payload);
    }

    protected function buildIssueBody(SupportTicket $ticket): string
    {
        $lines = [
            '## Feedback utilisateur',
            '',
            sprintf('- **Nom :** %s', $ticket->name ?? 'Anonyme'),
            sprintf('- **Email :** %s', $ticket->email ?? 'Non fourni'),
            sprintf('- **Catégorie :** %s', ucfirst((string) $ticket->category)),
            sprintf('- **Ticket ID :** %s', $ticket->id),
            sprintf('- **Créé le :** %s', optional($ticket->created_at)->toDateTimeString() ?? now()->toDateTimeString()),
            '',
            '---',
            '',
            '### Message',
            $ticket->message ?? '_(vide)_',
        ];

        return implode("\n", $lines);
    }
}
