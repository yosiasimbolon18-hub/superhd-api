<?php
namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class EnsureAdmin
{
    // Memastikan hanya akun tabel `users` (admin/staf) yang bisa akses grup route /admin/*.
    // Pelanggan & Distributor yang sudah login tetap DITOLAK walau token Sanctum-nya valid.
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user() instanceof User) {
            return response()->json(['message' => 'Akses ditolak. Endpoint ini khusus administrator.'], 403);
        }
        if (! $request->user()->is_active) {
            return response()->json(['message' => 'Akun admin ini dinonaktifkan.'], 403);
        }
        return $next($request);
    }
}
