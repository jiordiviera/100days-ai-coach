<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AllowTelegramWebhook
{
    public function handle(Request $request, Closure $next): Response
    {
        // Désactiver la vérification CSRF pour le webhook Telegram
        if ($request->is('api/telegram/*')) {
            config(['session.driver' => 'array']);

            return $next($request)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type');
        }

        return $next($request);
    }
}
