<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware: ForceJsonResponse
 *
 * Memastikan seluruh request ke /api/* menerima response JSON,
 * bahkan saat terjadi exception (401 Unauthenticated, dll.)
 * yang normalnya akan men-redirect ke halaman login HTML.
 *
 * Cukup set header Accept: application/json — Laravel
 * akan mengembalikan JSON untuk semua error response.
 */
class ForceJsonResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
