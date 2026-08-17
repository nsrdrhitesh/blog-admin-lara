<?php

namespace App\Http\Middleware;

use App\Services\RedirectService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Runs after the app would otherwise 404. Checks the redirects table (both
 * manually created ones from the Redirect Manager, and the automatic ones
 * HasSlug writes when a Blog/Category/Author/Page/Tag is renamed — see
 * Phase 1) and issues the configured redirect instead of a 404.
 *
 * Registered as global web middleware in bootstrap/app.php — it's a no-op
 * (near-zero cost, one indexed lookup) for the 404 case only; every normal
 * 200 response short-circuits before the redirect lookup ever runs.
 */
class CheckRedirects
{
    public function __construct(protected RedirectService $redirects) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->getStatusCode() !== 404) {
            return $response;
        }

        $redirect = $this->redirects->findActiveFor($request->path());

        if (! $redirect) {
            return $response;
        }

        $redirect->increment('hits');

        return redirect($redirect->to_url, $redirect->status_code);
    }
}
