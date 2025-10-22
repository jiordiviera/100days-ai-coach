<?php

namespace App\Console\Commands\Telegram;

use App\Services\Telegram\TelegramClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;

class SetWebhook extends Command
{
    protected $signature = 'telegram:set-webhook {url?}';
    protected $description = 'Définir l\'URL du webhook pour le bot Telegram';

    public function handle(TelegramClient $telegram)
    {
        $url = $this->argument('url') ?: URL::route('api.telegram.webhook');
        
        // Si l'URL ne commence pas par https, ajoutez votre domaine
        if (!str_starts_with($url, 'https')) {
            $url = config('app.url') . '/' . ltrim($url, '/');
        }

        $this->info("Configuration du webhook vers : {$url}");

        try {
            $response = Http::post("https://api.telegram.org/bot" . config('services.telegram.bot_token') . "/setWebhook", [
                'url' => $url
            ]);

            if ($response->successful()) {
                $this->info('Webhook configuré avec succès !');
                $this->line(json_encode($response->json(), JSON_PRETTY_PRINT));
            } else {
                $this->error('Erreur lors de la configuration du webhook : ' . $response->body());
            }
        } catch (\Exception $e) {
            $this->error('Erreur : ' . $e->getMessage());
        }
    }
}
