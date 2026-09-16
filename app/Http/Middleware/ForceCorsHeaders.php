<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Middleware CORS manual — dipasang sebagai pengganti/pengaman karena
 * mekanisme CORS bawaan Laravel (config/cors.php) sempat gagal nambahin
 * header Access-Control-Allow-Origin di beberapa kondisi lokal (dev di
 * Windows/Laragon). Middleware ini memaksa header CORS selalu ada di
 * SETIAP respons dari /api/*, dan langsung membalas request OPTIONS
 * (preflight) dengan 204 tanpa perlu masuk ke controller.
 *
 * Untuk production nanti, sebaiknya ganti '*' di bawah dengan domain
 * frontend yang sebenarnya, misal: https://superhdkliner.com
 */
class ForceCorsHeaders
{
    public function handle(Request $request, Closure $next)
    {
        // Request OPTIONS (preflight) langsung dibalas di sini, gak perlu
        // lanjut ke controller.
        if ($request->getMethod() === 'OPTIONS') {
            $response = response('', 204);
        } else {
            $response = $next($request);
        }

        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept');
        $response->headers->set('Access-Control-Max-Age', '0');

        return $response;
    }
}
