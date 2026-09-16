<?php

use Illuminate\Support\Facades\Route;

// Backend ini sekarang API-only. Halaman frontend (index.html, css, js)
// sudah dipindah ke folder /frontend terpisah dan tidak lagi di-serve
// dari sini — routing user-facing diatur di reverse proxy (lihat
// deploy/nginx.conf.example di root project).

Route::get('/', function () {
    return response()->json([
        'service' => 'Super HD Kliner API',
        'status' => 'ok',
    ]);
});
