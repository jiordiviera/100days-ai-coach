<?php

namespace App\Console\Commands;

use App\Models\ChallengeRun;
use App\Models\UserProfile;
use Illuminate\Console\Command;
use Spatie\Sitemap\SitemapGenerator;
use Spatie\Sitemap\Tags\Url;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: "generate:sitemap")]
class GenerateSitemap extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates a sitemap for the public site.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sitemap = SitemapGenerator::create(config('app.url'))
            ->getSitemap();

        $sitemap->add(
            Url::create(route('home'))
                ->setPriority(1.0)
                ->setChangeFrequency('weekly')
        );

        $profiles = UserProfile::query()
            ->where('is_public', true)
            ->whereNotNull('username')
            ->orderBy('updated_at', 'desc')
            ->limit(200)
            ->get(['username', 'updated_at']);

        $challenges = ChallengeRun::query()
            ->where('is_public', true)
            ->whereNotNull('public_slug')
            ->orderBy('updated_at', 'desc')
            ->limit(200)
            ->get(['public_slug', 'updated_at']);

        foreach ($profiles as $profile) {
            $this->withProgressBar($profiles, function ($profile) use ($sitemap) {

                $sitemap->add(
                    Url::create(route('public.profile', ['username' => $profile->username]))
                        ->setPriority(0.8)
                        ->setChangeFrequency('weekly')
                );
            });
        }

        foreach ($challenges as $challenge) {
            $sitemap->add(
                Url::create(route('public.challenge', ['slug' => $challenge->public_slug]))
                    ->setPriority(0.8)
                    ->setChangeFrequency('weekly')
            );
        }

        $sitemap->writeToDisk('public', 'sitemap.xml');
    }
}
