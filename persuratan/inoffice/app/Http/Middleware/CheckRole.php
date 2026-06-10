<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Cek apakah user memiliki salah satu role yang dibutuhkan.
     * Usage: Route::middleware(['auth', 'role:pimpinan,admin-it'])
     *
     * Support JSON response untuk API requests (mobile),
     * redirect HTML tetap berlaku untuk web requests.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Super admin bypass semua
        if ($user->hasRole('super-admin')) {
            return $next($request);
        }

        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Forbidden. Anda tidak memiliki izin untuk mengakses resource ini.',
            ], 403);
        }

        abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
    }
}

