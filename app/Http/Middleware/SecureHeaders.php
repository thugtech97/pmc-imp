<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecureHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        if (function_exists('header_remove')) {
            header_remove('X-Powered-By');
        }

        $response = $next($request);

        // 1. Removed Strict-Transport-Security (HSTS) completely since it requires HTTPS
        $headers = [
            'Permissions-Policy' => 'geolocation=(), camera=(), microphone=()',
            'Cross-Origin-Embedder-Policy' => 'require-corp',
            'Cross-Origin-Opener-Policy' => 'same-origin',
            'Cross-Origin-Resource-Policy' => 'same-origin',
            'X-Permitted-Cross-Domain-Policies' => 'none',
            'X-Frame-Options' => 'DENY',
            'X-Content-Type-Options' => 'nosniff',
        ];

        // 2. Removed 'upgrade-insecure-requests' from the end of the CSP string
        // Update your CSP string to whitelist Google Fonts and Ionicons
        $headers['Content-Security-Policy'] = "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline' 'unsafe-eval'; " .
            // Allow styles from self, inline styles, Google Fonts, and Ionicons
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://code.ionicframework.com; " .
            // Allow the actual font files from self, Google, and Ionicons
            "font-src 'self' https://fonts.gstatic.com https://code.ionicframework.com; " .
            "img-src 'self' data: blob:; " .
            "object-src 'none'; " .
            "base-uri 'self'; " .
            "frame-ancestors 'none'; " .
            "form-action 'self';";

        foreach ($headers as $key => $value) {
            $response->headers->set($key, $value);
        }

        // Remove X-Powered-By header to prevent information disclosure
        $response->headers->set('X-Powered-By', '');

        return $response;
    }
}