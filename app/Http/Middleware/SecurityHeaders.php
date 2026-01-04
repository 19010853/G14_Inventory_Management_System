<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     * Add security headers to improve website security and help remove Google Safe Browsing warnings.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $response = $next($request);

            // Only set headers if response is successful and is a Response instance
            if ($response instanceof Response && method_exists($response, 'getStatusCode') && $response->getStatusCode() < 400) {
                // Prevent MIME type sniffing
                $response->headers->set('X-Content-Type-Options', 'nosniff');
                
                // Prevent clickjacking attacks
                $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
                
                // Enable XSS protection (legacy, but still useful)
                $response->headers->set('X-XSS-Protection', '1; mode=block');
                
                // Control referrer information
                $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
                
                // Control browser features
                $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
                
                // Content Security Policy - Adjust based on your needs
                // This allows scripts from your domain, CDNs, and inline scripts (for Laravel)
                $csp = "default-src 'self'; " .
                       "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://ajax.googleapis.com https://fonts.googleapis.com; " .
                       "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net; " .
                       "font-src 'self' https://fonts.gstatic.com data:; " .
                       "img-src 'self' data: https: blob:; " .
                       "connect-src 'self' https:; " .
                       "frame-ancestors 'self';";
                $response->headers->set('Content-Security-Policy', $csp);
                
                // HSTS - Force HTTPS (only in production with valid SSL)
                if (config('app.env') === 'production' && $request->secure()) {
                    $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
                }
            }

            return $response;
        } catch (\Exception $e) {
            \Log::error('SecurityHeaders middleware error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            // Return response without headers if there's an error
            return $next($request);
        }
    }
}
