<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product\Product;
use App\Models\Product\Variant;
use Illuminate\Support\Str;

class CleanVariantNamesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product:clean-variants
                            {--product= : ID atau Code Produk tertentu (opsional)}
                            {--sku= : SKU varian tertentu (opsional)}
                            {--all : Bersihkan semua varian termasuk normalisasi dimensi (misal: 200 X 160 -> 160 X 200)}
                            {--dry-run : Pratinjau perubahan tanpa menyimpan ke database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleansing variant_name yang sama dengan SKU atau belum diformat ukuran (misal: S200080 -> 080 X 200)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $productId = $this->option('product');
        $skuOpt = $this->option('sku');
        $all = (bool) $this->option('all');
        $dryRun = (bool) $this->option('dry-run');

        $this->info('Memulai proses cleansing variant_name...');
        if ($dryRun) {
            $this->warn('MODE DRY-RUN: Data TIDAK akan diubah di database.');
        }

        $query = Variant::with('product');

        if ($productId) {
            $query->where(function ($q) use ($productId) {
                $q->where('product_id', $productId)
                  ->orWhereHas('product', function ($pq) use ($productId) {
                      $pq->where('code', $productId);
                  });
            });
        }

        if ($skuOpt) {
            $query->where('sku', $skuOpt);
        }

        $variants = $query->get();

        if ($variants->isEmpty()) {
            $this->warn('Tidak ada data varian yang ditemukan sesuai kriteria.');
            return 0;
        }

        $updatedCount = 0;
        $previewData = [];

        foreach ($variants as $variant) {
            $sku = trim((string) $variant->sku);
            $oldName = trim((string) $variant->variant_name);
            $product = $variant->product;

            $needsCleaning = false;
            $newName = $oldName;
            $extractedWidth = null;
            $extractedLength = null;

            // 1. Cek apakah variant_name sama dengan SKU atau berupa kode SKU mentah
            $isSameAsSku = (strcasecmp($sku, $oldName) === 0);
            $isRawSku = (!str_contains($oldName, ' ') && strlen($oldName) >= 12 && preg_match('/[0-9]/', $oldName));
            $isInvalidName = ($oldName === '.' || $oldName === '-' || empty($oldName));

            if ($isSameAsSku || $isRawSku || $isInvalidName) {
                $needsCleaning = true;
                [$newName, $extractedWidth, $extractedLength] = $this->resolveNameFromSkuOrAttributes($sku, $variant);
            } elseif ($all) {
                // Mode --all: Cek juga normalisasi format terbalik (misal "200 X 080" atau "BIRU 200 X 090")
                $normalized = $this->normalizeDimensionOrder($oldName, $sku, $variant);
                if ($normalized !== null && $normalized['name'] !== $oldName) {
                    $needsCleaning = true;
                    $newName = $normalized['name'];
                    $extractedWidth = $normalized['width'];
                    $extractedLength = $normalized['length'];
                }
            }

            if ($needsCleaning && $newName !== $oldName) {
                $updatedCount++;

                $previewData[] = [
                    'product' => Str::limit($product ? $product->name : 'N/A', 25),
                    'sku' => $sku,
                    'old_name' => $oldName,
                    'new_name' => $newName,
                ];

                if (!$dryRun) {
                    $attrs = is_array($variant->attributes) ? $variant->attributes : [];

                    if ($extractedWidth !== null && $extractedWidth > 0) {
                        $variant->width = $extractedWidth;
                        $attrs['width'] = $extractedWidth;
                    }
                    if ($extractedLength !== null && $extractedLength > 0) {
                        $variant->length = $extractedLength;
                        $attrs['length'] = $extractedLength;
                    }
                    $attrs['status'] = true;

                    $variant->variant_name = $newName;
                    $variant->attributes = $attrs;
                    $variant->save();
                }
            }
        }

        // Tampilkan tabel hasil
        if (!empty($previewData)) {
            $this->table(
                ['Produk', 'SKU', 'Variant Name Lama', 'Variant Name Baru'],
                array_slice($previewData, 0, 50)
            );

            if (count($previewData) > 50) {
                $this->info('... dan ' . (count($previewData) - 50) . ' varian lainnya.');
            }
        }

        if ($dryRun) {
            $this->warn("Total varian yang akan diubah (Dry-Run): {$updatedCount}");
        } else {
            $this->info("Berhasil membersihkan {$updatedCount} varian.");
        }

        return 0;
    }

    /**
     * Ekstrak ukuran dari pola SKU JDE [S|T][Panjang(3)][Lebar(3)]
     * Contoh: KBR100010011348S200080 -> Panjang: 200, Lebar: 80 -> "080 X 200"
     */
    private function resolveNameFromSkuOrAttributes(string $sku, Variant $variant): array
    {
        $width = null;
        $length = null;

        // Cek pola standar SKU POS JDE: S200080, T200075, S190115, dll.
        if (preg_match('/[ST](\d{3})(\d{3})$/i', $sku, $matches)) {
            $lengthStr = $matches[1];
            $widthStr = $matches[2];
            $length = (int) $lengthStr;
            $width = (int) $widthStr;

            if ($length === 0 && $width === 0) {
                return ['Standar', 0, 0];
            }

            if ($length === 0 && $width > 0) {
                // Kasus seperti Headboard (Sandaran) yang hanya ada lebar
                return [(string) $width, $width, 0];
            }

            // Standar kasur: Lebar X Panjang (misal: 080 X 200)
            return ["{$widthStr} X {$lengthStr}", $width, $length];
        }

        // Fallback: Gunakan attributes jika ada
        $w = $variant->width ?? $variant->attributes['width'] ?? null;
        $l = $variant->length ?? $variant->attributes['length'] ?? null;

        if ($w !== null && $l !== null) {
            $w = (int) $w;
            $l = (int) $l;
            if ($w > 0 && $l > 0) {
                return [sprintf('%03d X %03d', $w, $l), $w, $l];
            }
            if ($w > 0 && $l === 0) {
                return [(string) $w, $w, 0];
            }
        }

        return ['Standar', 0, 0];
    }

    /**
     * Normalisasi nama varian yang terbalik (Panjang X Lebar -> Lebar X Panjang)
     * Contoh: "200 X 080" -> "080 X 200", "BIRU 200 X 160" -> "BIRU 160 X 200"
     */
    private function normalizeDimensionOrder(string $oldName, string $sku, Variant $variant): ?array
    {
        // Pola: [Prefix opsional] [Angka 3 digit] X [Angka 3 digit] [Suffix opsional]
        if (preg_match('/^(.*?)(?:\s+|^)(\d{3})\s*[xX]\s*(\d{3})(.*)$/', $oldName, $m)) {
            $prefix = trim($m[1]);
            $num1 = (int) $m[2]; // Kemungkinan panjang (misal 200 atau 190)
            $num2 = (int) $m[3]; // Kemungkinan lebar (misal 80, 90, 100, 120, 140, 160, 180)
            $suffix = trim($m[4]);

            // Jika angka pertama adalah panjang standar (180, 190, 200, 210, 213, 240) dan angka kedua adalah lebar kasur
            $commonLengths = [180, 190, 200, 210, 213, 240];
            if (in_array($num1, $commonLengths) && $num1 > $num2) {
                $newDim = sprintf('%03d X %03d', $num2, $num1);
                $parts = [];
                if ($prefix !== '') $parts[] = $prefix;
                $parts[] = $newDim;
                if ($suffix !== '') $parts[] = $suffix;

                return [
                    'name' => implode(' ', $parts),
                    'width' => $num2,
                    'length' => $num1
                ];
            }
        }

        return null;
    }
}
