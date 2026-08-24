<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateTableToDev extends Command
{
    // Command sekarang tidak membutuhkan nama tabel, jalankan saja: php artisan migrate:all-to-dev
    protected $signature = 'migrate:all-to-dev';

    protected $description = 'Memindahkan data SEMUA tabel dari DB lokal ke DB dev';

    public function handle()
    {
        $this->info("Memulai migrasi seluruh data tabel ke server dev...");

        $devConnection = 'dev';

        // Mendapatkan semua nama tabel di database lokal (asumsi menggunakan PostgreSQL lokal)
        $tables = collect(DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'"))
                    ->pluck('table_name')
                    ->toArray();

        // Jika Anda menggunakan MySQL di lokal, comment kode di atas dan uncomment kode di bawah ini:
        // $tables = collect(DB::select("SHOW TABLES"))->map(function ($val) { return array_values((array) $val)[0]; })->toArray();

        // Mengabaikan tabel migrations agar tidak terjadi konflik
        $tables = array_filter($tables, function ($table) {
            return $table !== 'migrations';
        });

        if (empty($tables)) {
            $this->warn("Tidak ditemukan tabel di database lokal.");
            return 0;
        }

        // Matikan sementara Foreign Key constraints di Dev (agar insert aman tanpa peduli urutan relasi tabel)
        try {
            Schema::connection($devConnection)->disableForeignKeyConstraints();
        } catch (\Exception $e) {
            // Abaikan jika user dev tidak memiliki hak akses mematikan FK
        }

        foreach ($tables as $tableName) {
            $this->info("\n=========================================");
            $this->info("Memproses Tabel: {$tableName}");

            $totalData = DB::table($tableName)->count();
            $this->info("Total data: {$totalData}");

            if ($totalData === 0) {
                $this->warn("Skip: Tabel kosong.");
                continue;
            }

            $bar = $this->output->createProgressBar($totalData);
            $bar->start();

            $records = [];
            // Menggunakan cursor() lebih aman daripada chunk() karena tidak membutuhkan kolom 'id' (cocok untuk pivot table)
            foreach (DB::table($tableName)->cursor() as $row) {
                $records[] = (array) $row;

                // Masukkan data setiap 500 baris agar RAM tidak penuh
                if (count($records) >= 500) {
                    DB::connection($devConnection)->table($tableName)->insert($records);
                    $bar->advance(count($records));
                    $records = [];
                }
            }

            // Masukkan sisa data yang kurang dari 500
            if (count($records) > 0) {
                DB::connection($devConnection)->table($tableName)->insert($records);
                $bar->advance(count($records));
            }

            $bar->finish();
            $this->newLine();
            $this->info("Selesai migrasi tabel {$tableName}.");
        }

        // Nyalakan kembali Foreign Key constraints
        try {
            Schema::connection($devConnection)->enableForeignKeyConstraints();
        } catch (\Exception $e) {}

        $this->info("\n=========================================");
        $this->info("SUKSES! Semua data dari tabel lokal telah dipindahkan ke server dev.");
        
        return 0;
    }
}
