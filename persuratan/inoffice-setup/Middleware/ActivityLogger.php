<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LogAktivitas;
use Symfony\Component\HttpFoundation\Response;

class ActivityLogger
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Log hanya untuk request yang mengubah data (POST, PUT, PATCH, DELETE)
        if (Auth::check() && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            try {
                LogAktivitas::create([
                    'user_id'     => Auth::id(),
                    'action'      => $request->method() . ' ' . $request->path(),
                    'entity_type' => null,
                    'entity_id'   => null,
                    'detail'      => json_encode([
                        'url'    => $request->fullUrl(),
                        'method' => $request->method(),
                    ]),
                    'ip_address'  => $request->ip(),
                    'user_agent'  => $request->userAgent(),
                    'timestamp'   => now(),
                ]);
            } catch (\Exception $e) {
                // Jangan gagalkan request karena logging error
            }
        }

        return $response;
    }
}
