<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Remove fingerprinting headers
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');
        $response->headers->remove('x-turbo-charged-by');

        // Security headers
        $response->headers->set('X-Frame-Options', 'deny');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        $response->headers->set('Referrer-Policy', 'no-referrer');
        $response->headers->set('Cross-Origin-Embedder-Policy', 'require-corp');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('X-Download-Options', 'noopen');
        $response->headers->set('Permissions-Policy',
            "geolocation=(), microphone=(), camera=(), fullscreen=(self)"
        );
        $response->headers->set('Content-Security-Policy',
            "default-src 'none'; " .
            "style-src 'self'; " .
            "script-src 'self'; " .
            "img-src 'self'; " .
            "connect-src 'self'; " .
            "form-action 'self'"
        );

        // HSTS — production only
        if (config('app.env') === 'production') {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );

            if (!$request->secure()) {
                return redirect()->secure($request->getRequestUri());
            }
        }

        return $response;
    }
}
