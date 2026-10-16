<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Cron Scheduler untuk memeriksa dan memproses antrean pengiriman JDE secara berkala.
// Dijalankan setiap 30 menit (bukan 5 menit) agar tidak membebani server/database (low workload),
// karena alur utama pengiriman sudah berjalan instan dan real-time via Queue Worker (PushOrderToJdeJob).
Schedule::command('jde:process-pending')
    ->everyThirtyMinutes()
    ->withoutOverlapping()
    ->runInBackground();
