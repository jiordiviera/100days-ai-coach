<?php

namespace App\Http\Controllers;

use App\Models\ChallengeRun;
use App\Models\UserProfile;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class PublicSitemapController extends Controller
{
    public function __invoke(): Response
    {
        $content = Cache::remember('public-sitemap', now()->addMinutes(30), function (): string {
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

            $urls = [];

            foreach ($profiles as $profile) {
                $urls[] = [
                    'loc' => route('public.profile', ['username' => $profile->username]),
                    'lastmod' => optional($profile->updated_at)->toAtomString(),
                ];
            }

            foreach ($challenges as $challenge) {
                $urls[] = [
                    'loc' => route('public.challenge', ['slug' => $challenge->public_slug]),
                    'lastmod' => optional($challenge->updated_at)->toAtomString(),
                ];
            }

            $xml = view('public.sitemap', ['urls' => $urls])->render();

            return trim($xml);
        });

        return response($content, 200)
            ->header('Content-Type', 'application/xml');
    }
}
