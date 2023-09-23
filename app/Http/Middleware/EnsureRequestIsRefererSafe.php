<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\Misc;

class EnsureRequestIsRefererSafe {
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response {
        if (Misc::isRefererSafe($request->server('HTTP_REFERER'))) return $next($request);
        else {
            return response()->json([
                'errors' => 'request denied'
            ], Response::HTTP_FORBIDDEN);
        }
    }
}