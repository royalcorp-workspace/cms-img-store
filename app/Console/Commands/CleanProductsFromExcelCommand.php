<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product\Product;
use App\Models\Product\Variant;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;

class CleanProductsFromExcelCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:clean-from-excel
                            {--file=cleaning.xlsx : Nama file excel di storage/ atau path lengkap}
                            {--all : Hapus semua produk di file excel tanpa memfilter kolom Keterangan}
                            {--dry-run : Pratinjau data produk & variasi yang akan diubah tanpa menyimpan ke database}
                            {--force : Jalankan langsung tanpa konfirmasi interaktif}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleaning produk dan variasi dari file Excel di storage menjadi deleted = true dan nonaktif';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $fileOpt = $this->option('file');
        $all = (bool) $this->option('all');
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        // 1. Resolve File Path
        $filePath = null;
        if (file_exists($fileOpt)) {
            $filePath = $fileOpt;
        } elseif (file_exists(storage_path($fileOpt))) {
            $filePath = storage_path($fileOpt);
        } elseif (file_exists(storage_path('app/' . $fileOpt))) {
            $filePath = storage_path('app/' . $fileOpt);
        }

        if (!$filePath || !file_exists($filePath)) {
            $this->error("File Excel tidak ditemukan di: {$fileOpt}");
            $this->line("Pastikan file berada di direktori storage/ (contoh: storage/cleaning.xlsx)");
            return Command::FAILURE;
        }

        $this->info("Memuat file Excel: {$filePath}...");

        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
        } catch (\Exception $e) {
            $this->error("Gagal membaca file Excel: " . $e->getMessage());
            return Command::FAILURE;
        }

        if (empty($rows) || count($rows) < 2) {
            $this->warn("File Excel kosong atau tidak memiliki data baris.");
            return Command::SUCCESS;
        }

        $header = array_map(fn($h) => strtoupper(trim((string) $h)), $rows[0]);
        $dataRows = array_slice($rows, 1);

        $this->line("Total baris data dalam Excel: " . count($dataRows));

        // Detect Columns
        $colKodeBarang = array_search('KODE BARANG', $header);
        if ($colKodeBarang === false) {
            $colKodeBarang = array_search('KODE', $header);
        }
        if ($colKodeBarang === false) {
            $colKodeBarang = 0; // Default column 0
        }

        $colKeterangan = array_search('KETERANGAN', $header);
        if ($colKeterangan === false) {
            $colKeterangan = 2; // Default column 2
        }

        $colKodeVariasi = array_search('KODE VARIASI', $header);
        if ($colKodeVariasi === false) {
            $colKodeVariasi = array_search('SKU', $header);
        }
        if ($colKodeVariasi === false) {
            $colKodeVariasi = 8; // Default column 8
        }

        $productCodes = [];
        $variantSkus = [];
        $skippedCount = 0;

        foreach ($dataRows as $r) {
            $rawCode = trim((string) ($r[$colKodeBarang] ?? ''));
            $keterangan = strtolower(trim((string) ($r[$colKeterangan] ?? '')));
            $rawSku = trim((string) ($r[$colKodeVariasi] ?? ''));

            if (empty($rawCode) && empty($rawSku)) {
                continue;
            }

            // If not --all, only process rows where keterangan is 'delete' (or empty)
            if (!$all && !empty($keterangan) && !str_contains($keterangan, 'delete')) {
                $skippedCount++;
                continue;
            }

            if (!empty($rawCode)) {
                $productCodes[] = $rawCode;
            }
            if (!empty($rawSku)) {
                $variantSkus[] = $rawSku;
            }
        }

        $uniqueProductCodes = array_values(array_unique(array_filter($productCodes)));
        $uniqueVariantSkus = array_values(array_unique(array_filter($variantSkus)));

        $this->info("Kode Produk unik untuk dibersihkan: " . count($uniqueProductCodes));
        if ($skippedCount > 0) {
            $this->comment("Dilewati (Keterangan bukan 'Delete'): {$skippedCount} baris");
        }

        // 2. Query Matching Products in Database
        $matchedProducts = Product::whereIn('code', $uniqueProductCodes)->get();
        $matchedProductIds = $matchedProducts->pluck('id')->toArray();

        // Also query variants belonging to matched products OR matching SKU
        $matchedVariantsQuery = Variant::where(function ($q) use ($matchedProductIds, $uniqueVariantSkus) {
            if (!empty($matchedProductIds)) {
                $q->whereIn('product_id', $matchedProductIds);
            }
            if (!empty($uniqueVariantSkus)) {
                $q->orWhereIn('sku', $uniqueVariantSkus);
            }
        });

        $matchedVariants = $matchedVariantsQuery->get();
        $matchedVariantIds = $matchedVariants->pluck('id')->toArray();

        $this->line("Ditemukan di Database:");
        $this->line("- Produk cocok: " . $matchedProducts->count() . " produk");
        $this->line("- Variasi cocok: " . $matchedVariants->count() . " variasi");

        if ($matchedProducts->isEmpty() && $matchedVariants->isEmpty()) {
            $this->warn("Tidak ada produk atau variasi di database yang cocok dengan data Excel.");
            return Command::SUCCESS;
        }

        // 3. Dry-Run Mode
        if ($dryRun) {
            $this->newLine();
            $this->warn("=== MODE DRY-RUN (TIDAK ADA PERUBAHAN KE DATABASE) ===");
            $sampleProducts = $matchedProducts->take(10)->map(function ($p) {
                return [
                    'ID' => $p->id,
                    'Kode' => $p->code,
                    'Nama Produk' => $p->name,
                    'Status Sekarang' => $p->deleted ? 'Deleted (True)' : ($p->status ? 'Aktif' : 'Nonaktif'),
                    'Aksi' => 'Set deleted=true, status=0, show_on_web=false',
                ];
            });

            $this->table(['ID', 'Kode', 'Nama', 'Status Sekarang', 'Aksi yang Direncanakan'], $sampleProducts);
            $this->info("Menampilkan 10 dari total {$matchedProducts->count()} produk yang akan diubah.");
            return Command::SUCCESS;
        }

        // 4. Confirmation
        if (!$force) {
            $confirm = $this->confirm("Apakah Anda yakin ingin menandai {$matchedProducts->count()} produk dan {$matchedVariants->count()} variasi sebagai Deleted (deleted=true)?", false);
            if (!$confirm) {
                $this->warn("Operasi dibatalkan oleh pengguna.");
                return Command::SUCCESS;
            }
        }

        // 5. Execute Update
        $this->info("Memperbarui status produk dan variasi...");
        $bar = $this->output->createProgressBar(2);
        $bar->start();

        DB::transaction(function () use ($matchedProductIds, $matchedVariantIds) {
            if (!empty($matchedProductIds)) {
                Product::whereIn('id', $matchedProductIds)->update([
                    'deleted' => true,
                    'status' => 0,
                    'show_on_web' => false,
                    'updated_at' => now(),
                ]);
            }

            if (!empty($matchedVariantIds)) {
                Variant::whereIn('id', $matchedVariantIds)->update([
                    'deleted' => true,
                    'status' => 0,
                    'updated_at' => now(),
                ]);
            }
        });

        $bar->advance();
        $bar->advance();
        $bar->finish();

        $this->newLine(2);
        $this->info("Cleaning produk berhasil diselesaikan!");
        $this->table(
            ['Metrik', 'Jumlah'],
            [
                ['Total Baris Excel', count($dataRows)],
                ['Kode Produk di Excel', count($uniqueProductCodes)],
                ['Produk Berhasil di-Delete (deleted=true)', count($matchedProductIds)],
                ['Variasi Berhasil di-Delete (deleted=true)', count($matchedVariantIds)],
            ]
        );

        return Command::SUCCESS;
    }
}
