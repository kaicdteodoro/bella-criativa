<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // Enforced CSP (compatibility-first): keeps current UI behavior stable.
        $isLocal = app()->environment('local');
        $devOrigins = $isLocal ? " http://localhost:5173 ws://localhost:5173" : '';

        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; base-uri 'self'; frame-ancestors 'self'; ".
            "img-src 'self' data: blob: https:; font-src 'self' data: https:; ".
            "style-src 'self' 'unsafe-inline' https:{$devOrigins}; script-src 'self' 'unsafe-inline' 'unsafe-eval' https:{$devOrigins}; ".
            "connect-src 'self' https:{$devOrigins}; form-action 'self' https://wa.me"
        );

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}

