<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

// ================================================================
// FALLBACK CORS — dipasang di titik paling awal (sebelum Laravel
// boot sama sekali), supaya header ini PASTI ada di setiap respons
// apapun yang terjadi di dalam. Request OPTIONS (preflight) langsung
// dijawab di sini juga, gak perlu masuk ke Laravel.
// ================================================================
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
