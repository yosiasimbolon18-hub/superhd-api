<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Backup database otomatis tiap hari jam 2 pagi.
// CATATAN: di lokal (Laragon), scheduler Laravel HANYA jalan kalau ada proses yang terus memanggilnya.
// Jalankan `php artisan schedule:work` di terminal terpisah dan biarkan tetap terbuka,
// atau di server production gunakan cron job yang memanggil `php artisan schedule:run` tiap menit.
Schedule::command('backup:database')->dailyAt('02:00');
