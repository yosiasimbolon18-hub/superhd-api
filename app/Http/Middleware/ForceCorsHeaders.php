<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceCorsHeaders
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->getMethod() === 'OPTIONS') {
            $response = response('', 204);
        } else {
            $response = $next($request);
        }

        // Hanya set header kalau belum ada (mencegah duplikat kalau
        // web server/Caddy juga menambahkan header yang sama).
        if (!$response->headers->has('Access-Control-Allow-Origin')) {
            $response->headers->set('Access-Control-Allow-Origin', '*');
        }
        if (!$response->headers->has('Access-Control-Allow-Methods')) {
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        }
        if (!$response->headers->has('Access-Control-Allow-Headers')) {
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept');
        }
        if (!$response->headers->has('Access-Control-Max-Age')) {
            $response->headers->set('Access-Control-Max-Age', '0');
        }

        return $response;
    }
}