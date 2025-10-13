<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user?->is_admin || !in_array($user->email, ["hello@jiordiviera.me", "jiordikengne@gmail.com"])) {
            abort(403);
        }

        return $next($request);
    }
}
