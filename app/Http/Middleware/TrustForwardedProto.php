<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrustForwardedProto
{
    /**
     * Ensure Laravel treats proxied HTTPS requests as secure (Cloudflare / reverse proxy).
     *
     * Livewire temporary upload URLs are signed with the request scheme; without this,
     * validation fails with 401 when the origin receives HTTP but the browser uses HTTPS.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isForwardedHttps($request)) {
            $request->server->set('HTTPS', 'on');
            $request->server->set('REQUEST_SCHEME', 'https');
        }

        return $next($request);
    }

    private function isForwardedHttps(Request $request): bool
    {
        if (strtolower((string) $request->header('X-Forwarded-Proto')) === 'https') {
            return true;
        }

        $cfVisitor = $request->header('CF-Visitor');

        if (is_string($cfVisitor) && $cfVisitor !== '') {
            $visitor = json_decode($cfVisitor, true);

            if (is_array($visitor) && ($visitor['scheme'] ?? null) === 'https') {
                return true;
            }
        }

        return false;
    }
}
