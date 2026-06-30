<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Product\Product;
use App\Models\Product\Brand;
use App\Models\Product\Category;
use App\Models\Product\Color;
use App\Models\Product\Variant;
use App\Models\Product\Tag;

class ImportProducts extends Command
{
    protected $signature = 'import:products';
    protected $description = 'Import products from storage/product_import.xlsx';

    public function handle(): void
    {
        $filePath = storage_path('product_import.xlsx');

        if (!file_exists($filePath)) {
            $this->error("File tidak ditemukan: {$filePath}");
            return;
        }

        $this->info('Membaca file Excel...');

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);

            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            $colorRows = $spreadsheet->getSheetByName('product_colors');
            $colorRows = $colorRows ? $colorRows->toArray(null, true, true, true) : [];

            $variantRows = $spreadsheet->getSheetByName('product_variants');
            $variantRows = $variantRows ? $variantRows->toArray(null, true, true, true) : [];
        } catch (\Throwable $e) {
            $this->error('Gagal membaca file Excel: ' . $e->getMessage());
            return;
        }

        if (count($rows) < 2) {
            $this->warn('File Excel kosong atau hanya berisi header.');
            return;
        }

        $headers = array_shift($rows);

        $this->info('Header yang terdeteksi: ' . implode(', ', $headers));
        $this->info('Total data: ' . (count($rows)));

        if (!empty($colorRows)) {
            $this->info('Sheet colors ditemukan dengan ' . (count($colorRows) - (count($colorRows) > 0 ? 1 : 0)) . ' data');
        }

        if (!empty($variantRows)) {
            $this->info('Sheet variants ditemukan dengan ' . (count($variantRows) - (count($variantRows) > 0 ? 1 : 0)) . ' data');
        }

        $colorRows = $this->groupRows($colorRows);
        $variantRows = $this->groupRows($variantRows);

        $success = 0;
        $failed = 0;
        $skipped = 0;

        $bar = $this->output->createProgressBar(count($rows));
        $bar->start();

        foreach ($rows as $index => $row) {
            try {
                $data = array_combine($headers, $row);

                if (empty($data['name'])) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                $brandId = $this->resolveBrandId($data['brand_name'] ?? null);
                $categoryId = $this->resolveCategoryId($data['category_name'] ?? null);

                $productData = [
                    'category_id' => $categoryId,
                    'brand_id' => $brandId,
                    'name' => $data['name'] ?? null,
                    'slug' => $this->generateSlug($data['name'], $data['slug'] ?? null),
                    'thumbnail' => $data['thumbnail'] ?? null,
                    'alt_text' => $data['alt_text'] ?? null,
                    'short_description' => $data['short_description'] ?? null,
                    'description' => $data['description'] ?? null,
                    'base_price' => $this->toDecimal($data['base_price'] ?? null),
                    'segments' => $this->toJson($data['segments'] ?? null),
                    'best_seller' => $this->toBoolean($data['best_seller'] ?? null),
                    'is_new' => $this->toBoolean($data['is_new'] ?? null),
                    'sort_order' => $this->toInteger($data['sort_order'] ?? null),
                    'status' => $this->toBoolean($data['status'] ?? true),
                    'creator' => $data['creator'] ?? null,
                    'editor' => $data['editor'] ?? null,
                    'deleted' => false,
                ];

                DB::beginTransaction();

                $existing = null;

                if (!empty($data['id'])) {
                    $existing = Product::where('id', $data['id'])->first();
                }

                if (!$existing) {
                    $existing = Product::where('slug', $productData['slug'])->first();
                }

                if ($existing) {
                    $oldId = $existing->id;

                    if (is_null($productData['category_id'])) {
                        $productData['category_id'] = $existing->category_id;
                    }
                    if (is_null($productData['brand_id'])) {
                        $productData['brand_id'] = $existing->brand_id;
                    }
                    if (!empty($data['id']) && $oldId !== $data['id']) {
                        $productData['id'] = $data['id'];
                    }
                    $existing->update($productData);
                    if (!empty($data['id']) && $oldId !== $data['id']) {
                        $existing->id = $data['id'];
                        DB::table('product_colors')->where('product_id', $oldId)->update(['product_id' => $data['id']]);
                        DB::table('product_variants')->where('product_id', $oldId)->update(['product_id' => $data['id']]);
                        DB::table('product_tag_relations')->where('product_id', $oldId)->update(['product_id' => $data['id']]);
                        DB::table('product_images')->where('product_id', $oldId)->update(['product_id' => $data['id']]);
                    }
                    $product = $existing;
                } else {
                    if (is_null($productData['category_id'])) {
                        $productData['category_id'] = $this->getDefaultCategoryId();
                    }
                    if (is_null($productData['brand_id'])) {
                        $productData['brand_id'] = $this->getDefaultBrandId();
                    }
                    $productData['id'] = $data['id'] ?? null;
                    $product = Product::create($productData);
                }

                $this->syncColorsFromSheet($product, $colorRows);
                $this->syncVariantsFromSheet($product, $variantRows);
                $this->syncTags($product, $data['tags'] ?? null);

                DB::commit();
                $success++;
            } catch (\Throwable $e) {
                $this->warn(''. $e->getMessage());
                DB::rollBack();
                $failed++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info("Import selesai.");
        $this->info("Berhasil : {$success}");
        $this->warn("Dilewati : {$skipped}");
        $this->error("Gagal   : {$failed}");

        if ($failed > 0) {
            $this->warn('Gunakan parameter --verbose untuk melihat detail error.');
        }
    }

    private function resolveBrandId(?string $name): ?string
    {
        if (empty($name)) {
            return null;
        }

        $brand = Brand::where('name', $name)->orWhere('slug', Str::slug($name))->first();

        if ($brand) {
            return $brand->id;
        }

        $brand = Brand::create([
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => null,
        ]);

        return $brand->id;
    }

    private function resolveCategoryId(?string $name): ?string
    {
        if (empty($name)) {
            return null;
        }

        $category = Category::where('name', $name)->orWhere('slug', Str::slug($name))->first();

        if ($category) {
            return $category->id;
        }

        $category = Category::create([
            'parent_id' => null,
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => null,
            'sort_order' => 0,
            'status' => true,
            'deleted' => false,
        ]);

        return $category->id;
    }

    private function getDefaultCategoryId(): string
    {
        $category = Category::orderBy('sort_order')->orderBy('name')->first();

        if ($category) {
            return $category->id;
        }

        throw new \RuntimeException('Tidak ada kategori di database. Buat setidaknya satu kategori terlebih dahulu.');
    }

    private function getDefaultBrandId(): string
    {
        $brand = Brand::orderBy('name')->first();

        if ($brand) {
            return $brand->id;
        }

        throw new \RuntimeException('Tidak ada brand di database. Buat setidaknya satu brand terlebih dahulu.');
    }

    private function groupRows(?array $rows): array
    {
        if (empty($rows)) {
            return [];
        }

        $headers = array_shift($rows);
        $grouped = [];
        $useId = in_array('product_id', array_map('strtolower', $headers));

        foreach ($rows as $row) {
            $data = array_combine($headers, $row);

            if ($useId) {
                $key = strtolower(trim((string) ($data['product_id'] ?? '')));
            } else {
                $key = strtolower(trim((string) ($data['product_name'] ?? '')));
            }

            if ($key === '' || $key === null) {
                continue;
            }

            $grouped[$key][] = $data;
        }

        return $grouped;
    }

    private function generateSlug(?string $name, ?string $customSlug): ?string
    {
        if (!empty($customSlug)) {
            return Str::slug(trim($customSlug));
        }

        if (!empty($name)) {
            return Str::slug(trim($name));
        }

        return null;
    }

    private function syncColorsFromSheet(Product $product, array $groupedRows): void
    {
        if (empty($groupedRows)) {
            return;
        }

        $productId = strtolower(trim((string) $product->id));
        $productName = strtolower(trim((string) $product->name));

        $rows = $groupedRows[$productId] ?? ($groupedRows[$productName] ?? null);

        if (empty($rows)) {
            $this->warn("syncColorsFromSheet: no rows found for product_id={$productId}, name={$productName}");
            $this->warn("syncColorsFromSheet: available keys: " . implode(', ', array_slice(array_keys($groupedRows), 0, 5)));
            return;
        }

        $product->colors()->delete();

        foreach ($rows as $color) {
            $colorData = [
                'color_name' => $color['color_name'] ?? null,
                'color_code' => $color['color_code'] ?? null,
                'status' => $this->toBoolean($color['status'] ?? true),
                'creator' => $color['creator'] ?? null,
                'editor' => $color['editor'] ?? null,
                'deleted' => false,
            ];

            if (!empty($color['id'])) {
                $colorData['id'] = $color['id'];
            }

            Color::create(array_merge(
                ['product_id' => $product->id],
                $colorData
            ));
        }
    }

    private function syncVariantsFromSheet(Product $product, array $groupedRows): void
    {
        if (empty($groupedRows)) {
            return;
        }

        $productId = strtolower(trim((string) $product->id));
        $productName = strtolower(trim((string) $product->name));

        $rows = $groupedRows[$productId] ?? ($groupedRows[$productName] ?? null);

        if (empty($rows)) {
            $this->warn("syncVariantsFromSheet: no rows found for product_id={$productId}, name={$productName}");
            $this->warn("syncVariantsFromSheet: available keys: " . implode(', ', array_slice(array_keys($groupedRows), 0, 5)));
            return;
        }

        $product->variants()->delete();

        foreach ($rows as $variant) {
            $variantData = [
                'sku' => $variant['sku'] ?? null,
                'variant_name' => $variant['variant_name'] ?? null,
                'width' => $this->toDecimal($variant['width'] ?? null),
                'length' => $this->toDecimal($variant['length'] ?? null),
                'height' => $this->toDecimal($variant['height'] ?? null),
                'weight' => $this->toDecimal($variant['weight'] ?? null),
                'price' => $this->toDecimal($variant['price'] ?? null),
                'stock_qty' => $this->toInteger($variant['stock_qty'] ?? null),
                'min_order_qty' => $this->toInteger($variant['min_order_qty'] ?? null),
                'sort_order' => $this->toInteger($variant['sort_order'] ?? null),
                'status' => $this->toBoolean($variant['status'] ?? true),
                'creator' => $variant['creator'] ?? null,
                'editor' => $variant['editor'] ?? null,
                'deleted' => false,
            ];

            if (!empty($variant['id'])) {
                $variantData['id'] = $variant['id'];
            }

            Variant::create(array_merge(
                ['product_id' => $product->id],
                $variantData
            ));
        }
    }

    private function syncColors(Product $product, ?string $colorsJson): void
    {
        $product->colors()->delete();

        if (empty($colorsJson)) {
            return;
        }

        $colors = json_decode($colorsJson, true);

        if (!is_array($colors)) {
            return;
        }

        foreach ($colors as $color) {
            Color::create(array_merge(
                ['product_id' => $product->id],
                [
                    'color_name' => $color['color_name'] ?? null,
                    'color_code' => $color['color_code'] ?? null,
                    'status' => $this->toBoolean($color['status'] ?? true),
                    'creator' => $color['creator'] ?? null,
                    'editor' => $color['editor'] ?? null,
                    'deleted' => false,
                ]
            ));
        }
    }

    private function syncVariants(Product $product, ?string $variantsJson): void
    {
        $product->variants()->delete();

        if (empty($variantsJson)) {
            return;
        }

        $variants = json_decode($variantsJson, true);

        if (!is_array($variants)) {
            return;
        }

        foreach ($variants as $variant) {
            Variant::create(array_merge(
                ['product_id' => $product->id],
                [
                    'sku' => $variant['sku'] ?? null,
                    'variant_name' => $variant['variant_name'] ?? null,
                    'width' => $this->toDecimal($variant['width'] ?? null),
                    'length' => $this->toDecimal($variant['length'] ?? null),
                    'height' => $this->toDecimal($variant['height'] ?? null),
                    'weight' => $this->toDecimal($variant['weight'] ?? null),
                    'price' => $this->toDecimal($variant['price'] ?? null),
                    'stock_qty' => $this->toInteger($variant['stock_qty'] ?? null),
                    'min_order_qty' => $this->toInteger($variant['min_order_qty'] ?? null),
                    'sort_order' => $this->toInteger($variant['sort_order'] ?? null),
                    'status' => $this->toBoolean($variant['status'] ?? true),
                    'creator' => $variant['creator'] ?? null,
                    'editor' => $variant['editor'] ?? null,
                    'deleted' => false,
                ]
            ));
        }
    }

    private function syncTags(Product $product, ?string $tagsString): void
    {
        if (empty($tagsString)) {
            return;
        }

        $tagNames = array_filter(array_map('trim', explode(',', $tagsString)));

        if (empty($tagNames)) {
            return;
        }

        $tagIds = [];

        foreach ($tagNames as $tagName) {
            $tag = Tag::where('name', $tagName)->orWhere('slug', Str::slug($tagName))->first();

            if (!$tag) {
                $tag = Tag::create([
                    'name' => $tagName,
                    'slug' => Str::slug($tagName),
                    'sort_order' => 0,
                    'status' => true,
                    'deleted' => false,
                ]);
            }

            $tagIds[] = $tag->id;
        }

        $product->tags()->sync($tagIds);
    }

    private function toDecimal($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) str_replace(['.', ','], ['', '.'], (string) $value);
    }

    private function toInteger($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function toBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (bool) ((int) $value);
        }

        $value = strtolower(trim((string) $value));

        return in_array($value, ['1', 'true', 'yes', 'y', 'on'], true);
    }

    private function toJson($value): ?array
    {
        if (empty($value)) {
            return null;
        }

        if (is_array($value)) {
            return array_values(array_filter($value));
        }

        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $decoded = json_decode($value, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return array_values(array_filter($decoded));
        }

        $parts = array_values(array_filter(array_map('trim', explode(',', $value))));

        return $parts ?: null;
    }
}
