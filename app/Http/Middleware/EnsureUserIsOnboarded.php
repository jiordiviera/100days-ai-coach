<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsOnboarded
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($request->expectsJson()) {
            return $next($request);
        }

        if (! $user || ! $user->needsOnboarding()) {
            return $next($request);
        }

        if ($request->routeIs([
            'onboarding.*',
            'logout',
            'register',
            'login',
            'password.*',
            'livewire.*'
        ])) {
            return $next($request);
        }

        return redirect()->route('onboarding.wizard');
    }
}
