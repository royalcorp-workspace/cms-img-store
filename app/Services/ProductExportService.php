<?php

namespace App\Services;

use App\Models\Product\Product;
use App\Models\Product\Variant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductExportService
{
    /**
     * Build product query with optional request filters.
     */
    public function buildQuery(?Request $request = null): Builder
    {
        $query = Product::with([
            'category',
            'brand',
            'variants' => function ($q) {
                $q->where('deleted', false)
                  ->orderBy('variant_name');
            }
        ])->where('deleted', false);

        if ($request) {
            if ($search = $request->query('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                      ->orWhere('code', 'ilike', "%{$search}%")
                      ->orWhereHas('variants', function ($q2) use ($search) {
                          $q2->where('sku', 'ilike', "%{$search}%");
                      });
                });
            }

            if ($categoryId = $request->query('category_id')) {
                $query->where('category_id', $categoryId);
            }

            if ($brandId = $request->query('brand_id')) {
                $query->where('brand_id', $brandId);
            }

            if ($courierType = $request->query('courier_type')) {
                $query->where('courier_type', $courierType);
            }
        }

        return $query->orderBy('name');
    }

    /**
     * Generate PhpSpreadsheet object matching Template Export Item Master.
     * Columns:
     * KODE BARANG, NAMA BARANG, JENIS BARANG, ARTIKEL, KAIN, PANJANG, LEBAR, KODE VARIASI, HARGA MODAL, HARGA JUAL
     */
    public function generateSpreadsheet(?Request $request = null): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Item Master');

        $headers = [
            'KODE BARANG',
            'NAMA BARANG',
            'JENIS BARANG',
            'ARTIKEL',
            'KAIN',
            'PANJANG',
            'LEBAR',
            'KODE VARIASI',
            'HARGA MODAL',
            'HARGA JUAL',
        ];

        // 1. Write Header Row
        $sheet->fromArray($headers, null, 'A1');

        // Style Header Row
        $headerRange = 'A1:J1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9'); // Light slate
        $sheet->getStyle($headerRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFCBD5E1');
        $sheet->getRowDimension(1)->setRowHeight(26);

        // 2. Query Products and Populate Rows
        $products = $this->buildQuery($request)->get();
        $rowIndex = 2;

        foreach ($products as $product) {
            $segments = is_array($product->segments) ? $product->segments : [];

            // KODE BARANG (Item Master Code)
            if (!empty($segments['segment1']) && !empty($segments['segment2'])) {
                $kodeBarang = trim(
                    ($segments['segment1'] ?? '') .
                    ($segments['segment2'] ?? '') .
                    ($segments['segment3'] ?? '') .
                    ($segments['segment4'] ?? '')
                );
            } else {
                $kodeBarang = $product->code ?: $product->slug;
            }

            $namaBarang = $product->name;
            $jenisBarang = $segments['segment1'] ?? ($product->category?->name ?? '');
            $artikel = $segments['segment2'] ?? '';
            $kain = $segments['segment3'] ?? '';

            if ($product->variants && $product->variants->isNotEmpty()) {
                foreach ($product->variants as $variant) {
                    $attr = is_array($variant->attributes) ? $variant->attributes : [];

                    // PANJANG
                    $panjang = $variant->length ?: ($attr['length'] ?? null);
                    if (!$panjang && preg_match('/(\d{3})(\d{3})$/', (string)$variant->sku, $m)) {
                        $panjang = $m[1];
                    }
                    if (!$panjang && preg_match('/(\d{3})\s*[xX]\s*(\d{2,3})/', (string)$variant->variant_name, $m)) {
                        $panjang = $m[1];
                    }
                    if (!$panjang) {
                        $panjang = $segments['segment5'] ?? ($product->length ?? '');
                    }

                    // LEBAR
                    $lebar = $variant->width ?: ($attr['width'] ?? null);
                    if (!$lebar && preg_match('/(\d{3})(\d{3})$/', (string)$variant->sku, $m)) {
                        $lebar = $m[2];
                    }
                    if (!$lebar && preg_match('/(\d{2,3})\s*[xX]\s*(\d{3})/', (string)$variant->variant_name, $m)) {
                        $lebar = $m[1];
                    }
                    if (!$lebar) {
                        $lebar = $segments['segment6'] ?? ($product->width ?? '');
                    }

                    // KODE VARIASI
                    $kodeVariasi = $variant->sku ?: ($kodeBarang . ($panjang ? $panjang : '') . ($lebar ? $lebar : ''));

                    // HARGA MODAL
                    $hargaModal = (float) ($variant->base_price ?? ($product->base_price ?? 0));

                    // HARGA JUAL
                    $hargaJual = (float) ($variant->sell_price ?? ($variant->price ?? ($product->base_price ?? 0)));

                    // Write Row
                    $sheet->setCellValueExplicit('A' . $rowIndex, (string)$kodeBarang, DataType::TYPE_STRING);
                    $sheet->setCellValueExplicit('B' . $rowIndex, (string)$namaBarang, DataType::TYPE_STRING);
                    $sheet->setCellValueExplicit('C' . $rowIndex, (string)$jenisBarang, DataType::TYPE_STRING);
                    $sheet->setCellValueExplicit('D' . $rowIndex, (string)$artikel, DataType::TYPE_STRING);
                    $sheet->setCellValueExplicit('E' . $rowIndex, (string)$kain, DataType::TYPE_STRING);
                    $sheet->setCellValueExplicit('F' . $rowIndex, (string)$panjang, DataType::TYPE_STRING);
                    $sheet->setCellValueExplicit('G' . $rowIndex, (string)$lebar, DataType::TYPE_STRING);
                    $sheet->setCellValueExplicit('H' . $rowIndex, (string)$kodeVariasi, DataType::TYPE_STRING);
                    $sheet->setCellValue('I' . $rowIndex, $hargaModal);
                    $sheet->setCellValue('J' . $rowIndex, $hargaJual);

                    $rowIndex++;
                }
            } else {
                // Product without variants
                $panjang = $product->length ?? ($segments['segment5'] ?? '');
                $lebar = $product->width ?? ($segments['segment6'] ?? '');
                $kodeVariasi = $product->code ?: $kodeBarang;
                $hargaModal = (float) ($product->base_price ?? 0);
                $hargaJual = (float) ($product->base_price ?? 0);

                $sheet->setCellValueExplicit('A' . $rowIndex, (string)$kodeBarang, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('B' . $rowIndex, (string)$namaBarang, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('C' . $rowIndex, (string)$jenisBarang, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('D' . $rowIndex, (string)$artikel, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('E' . $rowIndex, (string)$kain, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('F' . $rowIndex, (string)$panjang, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('G' . $rowIndex, (string)$lebar, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('H' . $rowIndex, (string)$kodeVariasi, DataType::TYPE_STRING);
                $sheet->setCellValue('I' . $rowIndex, $hargaModal);
                $sheet->setCellValue('J' . $rowIndex, $hargaJual);

                $rowIndex++;
            }
        }

        $lastRow = max(2, $rowIndex - 1);

        // Format Price Columns (I & J) as Number with thousands separator
        $sheet->getStyle("I2:J{$lastRow}")
            ->getNumberFormat()
            ->setFormatCode('#,##0');

        // Alignments
        $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("C2:E{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("F2:G{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("H2:H{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("I2:J{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Auto-size columns A to J
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    /**
     * Stream Excel file directly as browser download.
     */
    public function download(?Request $request = null, ?string $filename = null): StreamedResponse
    {
        $spreadsheet = $this->generateSpreadsheet($request);
        $writer = new Xlsx($spreadsheet);

        if (!$filename) {
            $filename = 'Export_Item_Master_' . date('Ymd_His') . '.xlsx';
        }

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0, no-cache, must-revalidate, proxy-revalidate',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Save spreadsheet to specific file path on server.
     */
    public function saveToFile(string $filePath, ?Request $request = null): string
    {
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $spreadsheet = $this->generateSpreadsheet($request);
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return $filePath;
    }
}
