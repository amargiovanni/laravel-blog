<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleRedirects
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only handle GET requests
        if (! $request->isMethod('GET')) {
            return $next($request);
        }

        $path = '/'.ltrim($request->path(), '/');

        // Get cached redirects
        $redirects = Redirect::getCachedRedirects();

        // Check if we have a redirect for this path
        if (isset($redirects[$path])) {
            $redirectData = $redirects[$path];

            // Record the hit asynchronously
            $this->recordHit($redirectData['id']);

            // Build the target URL with query string preserved
            $targetUrl = $redirectData['target_url'];
            if ($request->getQueryString()) {
                $targetUrl .= '?'.$request->getQueryString();
            }

            return redirect($targetUrl, $redirectData['status_code']);
        }

        return $next($request);
    }

    /**
     * Record a redirect hit.
     */
    protected function recordHit(int $redirectId): void
    {
        $redirect = Redirect::find($redirectId);
        if ($redirect) {
            $redirect->recordHit();
        }
    }
}
