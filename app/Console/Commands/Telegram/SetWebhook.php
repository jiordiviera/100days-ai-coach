<?php

namespace App\Console\Commands\Telegram;

use App\Services\Telegram\TelegramClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;

class SetWebhook extends Command
{
    protected $signature = 'telegram:set-webhook {url?}';
    protected $description = 'Define the webhook URL for the Telegram bot';

    public function handle(TelegramClient $telegram)
    {
        $url = $this->argument('url') ?: URL::route('api.telegram.webhook');

        if (! str_starts_with($url, 'https')) {
            $url = config('app.url').'/'.ltrim($url, '/');
        }

        $this->info("Configuration of the webhook : {$url}");

        try {
            $response = Http::post("https://api.telegram.org/bot" . config('services.telegram.bot_token') . "/setWebhook", [
                'url' => $url
            ]);

            if ($response->successful()) {
                $this->info('Webhook configured successfully !');
                $this->line(json_encode($response->json(), JSON_PRETTY_PRINT));

                $this->setBotCommands();
            } else {
                $this->error('Error configuring the webhook : ' . $response->body());
            }
        } catch (\Exception $e) {
            $this->error('Error : ' . $e->getMessage());
        }
    }

    protected function setBotCommands(): void
    {
        $token = config('services.telegram.bot_token');

        if (blank($token)) {
            $this->warn('Unable to configure commands: the Telegram token is missing.');

            return;
        }

        $endpoint = "https://api.telegram.org/bot{$token}/setMyCommands";

        $locales = [
            'en' => [
                ['command' => 'start', 'description' => 'Show the welcome message'],
                ['command' => 'help', 'description' => 'List available commands'],
                ['command' => 'signup', 'description' => 'Open the sign-up page with Telegram linked'],
                ['command' => 'language', 'description' => 'Choose notification language (en|fr)'],
                ['command' => 'support', 'description' => 'Open the support centre'],
                ['command' => 'stop', 'description' => 'Pause Telegram notifications'],
            ],
            'fr' => [
                ['command' => 'start', 'description' => "Afficher le message d'accueil"],
                ['command' => 'help', 'description' => 'Lister les commandes disponibles'],
                ['command' => 'signup', 'description' => "Ouvrir l'inscription avec Telegram relié"],
                ['command' => 'language', 'description' => 'Choisir la langue des notifications (en|fr)'],
                ['command' => 'support', 'description' => 'Ouvrir le centre support'],
                ['command' => 'stop', 'description' => 'Suspendre les notifications Telegram'],
            ],
        ];

        foreach ($locales as $language => $commands) {
            $response = Http::asForm()->post($endpoint, [
                'commands' => json_encode($commands, JSON_UNESCAPED_UNICODE),
                'language_code' => $language,
            ]);

            if ($response->failed()) {
                $this->warn("Unable to update commands for {$language} : ".$response->body());
            } else {
                $this->info("Telegram commands updated for language {$language}.");
            }
        }
    }
}
