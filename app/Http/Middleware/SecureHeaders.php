<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecureHeaders
{
    /**
     * Agrega cabeceras de seguridad básicas a todas las respuestas web.
     * (OWASP A05: Security Misconfiguration).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Evita divulgación de la versión de PHP (OWASP A05 / CWE-497).
        // PHP añade X-Powered-By a nivel de SAPI, por eso hace falta header_remove().
        $response->headers->remove('X-Powered-By');
        if (function_exists('header_remove')) {
            header_remove('X-Powered-By');
        }

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // Content Security Policy (CWE-693).
        // - 'unsafe-inline': las vistas usan <script> y estilos en línea.
        // - 'unsafe-eval': Alpine.js compila sus expresiones con new Function().
        // - fonts.googleapis.com / fonts.gstatic.com: tipografías del tema.
        // El resto de dependencias (Bootstrap, SweetAlert) se empaquetan con Vite.
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; ".
            "img-src 'self' data:; ".
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; ".
            "script-src 'self' 'unsafe-inline' 'unsafe-eval'; ".
            "font-src 'self' data: https://fonts.gstatic.com; ".
            "connect-src 'self'; ".
            "object-src 'none'; ".
            "base-uri 'self'; ".
            "form-action 'self'; ".
            "frame-ancestors 'self'"
        );

        return $response;
    }
}
