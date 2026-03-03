<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * H-04: Security Headers Middleware
 * Menambahkan HTTP security headers untuk mencegah XSS, Clickjacking, MIME sniffing, dll.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Cegah clickjacking — aktif di semua environment
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Cegah MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // XSS Protection (legacy browsers)
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Kontrol Referrer information
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Batasi fitur browser yang tidak dibutuhkan
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        // HSTS: paksa HTTPS (hanya jika request sudah HTTPS)
        if ($request->isSecure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        // CSP: hanya di production
        // Saat local/dev, Vite dev server berjalan di port berbeda (5173)
        // yang akan diblokir CSP — jadi dinonaktifkan saat development.
        if (app()->environment('production', 'staging')) {
            $response->headers->set(
                'Content-Security-Policy',
                implode('; ', [
                    "default-src 'self'",
                    "script-src 'self' 'unsafe-inline' 'unsafe-eval'",
                    "style-src 'self' 'unsafe-inline' fonts.googleapis.com",
                    "font-src 'self' fonts.gstatic.com data:",
                    "img-src 'self' data: blob:",
                    "connect-src 'self'",
                    "frame-ancestors 'self'",
                    "form-action 'self'",
                ])
            );
        }

        return $response;
    }
}
