<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    Illuminate\Http\Request::capture()
);

try {
    Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    echo "Migrasi Berhasil!\n";
    echo Illuminate\Support\Facades\Artisan::output();
} catch (\Exception $e) {
    echo "Gagal: " . $e->getMessage();
}
