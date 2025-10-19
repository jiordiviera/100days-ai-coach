<?php

namespace App\Console\Commands;

use App\Models\ChallengeRun;
use App\Models\UserProfile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use SimpleXMLElement;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'generate:sitemap')]
class GenerateSitemap extends Command
{
    /**
     * The console command description.
     */
    protected $description = 'Génère un sitemap XML simple pour les pages publiques.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $baseUrls = collect([
            [
                'loc' => route('home'),
                'priority' => '1.0',
                'changefreq' => 'weekly',
                'lastmod' => now(),
            ],
        ]);

        $baseUrls = $baseUrls->merge([
            [
                'loc' => route('support'),
                'priority' => '0.6',
                'changefreq' => 'monthly',
                'lastmod' => null,
            ],
            [
                'loc' => route('legal.notice'),
                'priority' => '0.4',
                'changefreq' => 'monthly',
                'lastmod' => null,
            ],
            [
                'loc' => route('privacy.policy'),
                'priority' => '0.4',
                'changefreq' => 'monthly',
                'lastmod' => null,
            ],
        ]);


        $profiles = UserProfile::query()
            ->where('is_public', true)
            ->whereNotNull('username')
            ->orderByDesc('updated_at')
            ->limit(200)
            ->get(['username', 'updated_at']);

        $profileUrls = collect();

        $this->withProgressBar($profiles, function (UserProfile $profile) use (&$profileUrls): void {
            $profileUrls->push([
                'loc' => route('public.profile', ['username' => $profile->username]),
                'priority' => '0.8',
                'changefreq' => 'weekly',
                'lastmod' => $profile->updated_at,
            ]);
        });

        $this->newLine();

        $challenges = ChallengeRun::query()
            ->where('is_public', true)
            ->whereNotNull('public_slug')
            ->orderByDesc('updated_at')
            ->limit(200)
            ->get(['public_slug', 'updated_at']);

        $challengeUrls = collect();

        $this->withProgressBar($challenges, function (ChallengeRun $run) use (&$challengeUrls): void {
            $challengeUrls->push([
                'loc' => route('public.challenge', ['slug' => $run->public_slug]),
                'priority' => '0.7',
                'changefreq' => 'weekly',
                'lastmod' => $run->updated_at,
            ]);
        });

        $this->newLine();

        $urls = $baseUrls
            ->merge($profileUrls)
            ->merge($challengeUrls);

        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset/>');
        $xml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        $urls->each(function (array $entry) use ($xml): void {
            $url = $xml->addChild('url');
            $url->addChild('loc', htmlspecialchars($entry['loc'], ENT_QUOTES | ENT_XML1));

            if ($entry['lastmod']) {
                $url->addChild('lastmod', $entry['lastmod']->tz('UTC')->toAtomString());
            }

            if (! empty($entry['changefreq'])) {
                $url->addChild('changefreq', $entry['changefreq']);
            }

            if (! empty($entry['priority'])) {
                $url->addChild('priority', $entry['priority']);
            }
        });

        Storage::disk('public')->put('sitemap.xml', $xml->asXML());

        $this->info('Sitemap généré : public/sitemap.xml');

        return self::SUCCESS;
    }
}
