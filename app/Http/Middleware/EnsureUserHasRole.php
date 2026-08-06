<?php

namespace App\Http\Middleware;

use App\Enums\RoleSlug;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        $allowed = collect($roles)->contains(fn ($role) => $user->hasRole(RoleSlug::from($role)));

        if (! $allowed) {
            abort(403, 'You do not have access to this section.');
        }

        return $next($request);
    }
}
