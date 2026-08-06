<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Placeholder hook — model-level LogsActivity trait handles the actual
 * writes. Reserved for request-level audit needs (e.g. login attempts).
 */
class LogActivityMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}
