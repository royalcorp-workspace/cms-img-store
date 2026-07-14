<?php

namespace App\Http\Controllers;

use App\Models\Product\Product;
use App\Models\Product\Color;
use App\Models\Product\Brand;
use App\Models\Product\Category;
use App\Models\Product\Variant;
use App\Models\Product\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::paginate(15);
        return view('pages.products.index', compact('products'));
    }

    public function create()
    {
        $product = new Product();
        return view('pages.products.create', compact('product'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug',
            'thumbnail' => 'nullable|string|max:255',
            'alt_text' => 'nullable|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'base_price' => 'nullable|numeric|min:0',
            'segments' => 'nullable|array',
            'segments.*' => 'nullable|string|max:255',
            'best_seller' => 'boolean',
            'is_new' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean',
            'category_id' => 'nullable|string|exists:categories,id',
            'brand_id' => 'nullable|string|exists:brands,id',
            'colors' => 'nullable|array',
            'colors.*.color_name' => 'nullable|string|max:255',
            'colors.*.color_code' => 'nullable|string|max:255',
            'colors.*.status' => 'boolean',
            'variants' => 'nullable|array',
            'variants.*.sku' => 'nullable|string|max:255',
            'variants.*.variant_name' => 'nullable|string|max:255',
            'variants.*.width' => 'nullable|numeric|min:0',
            'variants.*.length' => 'nullable|numeric|min:0',
            'variants.*.height' => 'nullable|numeric|min:0',
            'variants.*.weight' => 'nullable|numeric|min:0',
            'variants.*.price' => 'nullable|numeric|min:0',
            'variants.*.stock_qty' => 'nullable|integer|min:0',
            'variants.*.min_order_qty' => 'nullable|integer|min:0',
            'variants.*.sort_order' => 'nullable|integer|min:0',
            'variants.*.status' => 'boolean',
        ]);

        $product = Product::create($validated);

        if (isset($validated['colors']) && is_array($validated['colors'])) {
            foreach ($validated['colors'] as $colorData) {
                Color::create(array_merge(['product_id' => $product->id], $colorData));
            }
        }

        if (isset($validated['variants']) && is_array($validated['variants'])) {
            foreach ($validated['variants'] as $variantData) {
                \App\Models\Product\Variant::create(array_merge(['product_id' => $product->id], $variantData));
            }
        }

        return redirect()->route('products.index')->with('success', 'Product created successfully');
    }

    public function show($id)
    {
        $product = Product::with('variants', 'colors')->findOrFail($id);
        return view('pages.products.show', compact('product'));
    }

    public function edit($id)
    {
        $product = Product::with('colors')->findOrFail($id);
        return view('pages.products.create', compact('product'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug,' . $product->id,
            'thumbnail' => 'nullable|string|max:255',
            'alt_text' => 'nullable|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'base_price' => 'nullable|numeric|min:0',
            'segments' => 'nullable|array',
            'segments.*' => 'nullable|string|max:255',
            'best_seller' => 'boolean',
            'is_new' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean',
            'category_id' => 'nullable|string|exists:categories,id',
            'brand_id' => 'nullable|string|exists:brands,id',
            'colors' => 'nullable|array',
            'colors.*.id' => 'nullable|string|exists:product_colors,id',
            'colors.*.color_name' => 'nullable|string|max:255',
            'colors.*.color_code' => 'nullable|string|max:255',
            'colors.*.status' => 'boolean',
            'variants' => 'nullable|array',
            'variants.*.id' => 'nullable|string|exists:product_variants,id',
            'variants.*.sku' => 'nullable|string|max:255',
            'variants.*.variant_name' => 'nullable|string|max:255',
            'variants.*.width' => 'nullable|numeric|min:0',
            'variants.*.length' => 'nullable|numeric|min:0',
            'variants.*.height' => 'nullable|numeric|min:0',
            'variants.*.weight' => 'nullable|numeric|min:0',
            'variants.*.price' => 'nullable|numeric|min:0',
            'variants.*.stock_qty' => 'nullable|integer|min:0',
            'variants.*.min_order_qty' => 'nullable|integer|min:0',
            'variants.*.sort_order' => 'nullable|integer|min:0',
            'variants.*.status' => 'boolean',
        ]);

        $product->update($validated);

        if (isset($validated['colors']) && is_array($validated['colors'])) {
            $submittedColorIds = [];
            foreach ($validated['colors'] as $colorData) {
                if (isset($colorData['id'])) {
                    $submittedColorIds[] = $colorData['id'];
                    $color = Color::find($colorData['id']);
                    if ($color) {
                        $color->update($colorData);
                    }
                } else {
                    Color::create(array_merge(['product_id' => $product->id], $colorData));
                }
            }

            if (!empty($submittedColorIds)) {
                $product->colors()->whereNotIn('id', $submittedColorIds)->delete();
            } else {
                $product->colors()->delete();
            }
        } else {
            $product->colors()->delete();
        }

        if (isset($validated['variants']) && is_array($validated['variants'])) {
            $submittedIds = [];
            foreach ($validated['variants'] as $variantData) {
                if (isset($variantData['id'])) {
                    $submittedIds[] = $variantData['id'];
                    $variant = \App\Models\Product\Variant::find($variantData['id']);
                    if ($variant) {
                        $variant->update($variantData);
                    }
                } else {
                    \App\Models\Product\Variant::create(array_merge(['product_id' => $product->id], $variantData));
                }
            }

            if (!empty($submittedIds)) {
                $product->variants()->whereNotIn('id', $submittedIds)->delete();
            } else {
                $product->variants()->delete();
            }
        } else {
            $product->variants()->delete();
        }

        return redirect()->route('products.index')->with('success', 'Product updated successfully');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return redirect()->route('products.index');
    }

    public function importForm()
    {
        return view('pages.products.import');
    }

    public function importStore(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:10240',
        ]);

        $file = $request->file('file');
        $filePath = $file->getRealPath();

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);

            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            $colorSheet = $spreadsheet->getSheetByName('product_colors');
            $colorRows = $colorSheet ? $colorSheet->toArray(null, true, true, true) : [];

            $variantSheet = $spreadsheet->getSheetByName('product_variants');
            $variantRows = $variantSheet ? $variantSheet->toArray(null, true, true, true) : [];
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membaca file Excel/CSV: ' . $e->getMessage());
        }

        if (count($rows) < 2) {
            return back()->with('error', 'File Excel/CSV kosong atau hanya berisi header.');
        }

        $headers = array_shift($rows);
        $headers = array_map(function($h) {
            return trim((string)$h);
        }, $headers);

        $colorRows = $this->groupRows($colorRows);
        $variantRows = $this->groupRows($variantRows);

        $success = 0;
        $failed = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            if (empty(array_filter($row))) {
                continue;
            }

            try {
                if (count($headers) !== count($row)) {
                    if (count($row) < count($headers)) {
                        $row = array_pad($row, count($headers), null);
                    } else {
                        $row = array_slice($row, 0, count($headers));
                    }
                }
                
                $data = array_combine($headers, $row);

                if (empty($data['name'])) {
                    $skipped++;
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
                    'creator' => $data['creator'] ?? Auth::user()->name ?? 'admin',
                    'editor' => $data['editor'] ?? Auth::user()->name ?? 'admin',
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
                    if (!empty($data['id'])) {
                        $productData['id'] = $data['id'];
                    }
                    $product = Product::create($productData);
                }

                $this->syncColorsFromSheet($product, $colorRows);
                $this->syncVariantsFromSheet($product, $variantRows);
                $this->syncTags($product, $data['tags'] ?? null);

                DB::commit();
                $success++;
            } catch (\Throwable $e) {
                DB::rollBack();
                $failed++;
                $errors[] = [
                    'row' => $index + 2,
                    'name' => $data['name'] ?? 'Unknown Name',
                    'message' => $e->getMessage()
                ];
            }
        }

        $result = [
            'success' => $success,
            'skipped' => $skipped,
            'failed' => $failed,
            'errors' => $errors,
        ];

        return back()->with('import_result', $result);
    }

    public function importTemplate()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // 1. Sheet Products
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('products');
        $headers = [
            'id', 'brand_name', 'category_name', 'name', 'slug', 'thumbnail', 
            'alt_text', 'short_description', 'description', 'base_price', 'segments', 
            'best_seller', 'is_new', 'sort_order', 'status', 'creator', 'editor', 'tags'
        ];
        $sheet->fromArray($headers, null, 'A1');
        
        $sampleProduct = [
            '', 'Samsung', 'Electronics', 'Samsung Galaxy S24', 'samsung-galaxy-s24', 'products/s4.jpg', 
            'Samsung Galaxy S24 phone image', 'Smartphone flagship Samsung S24', 'Full description of Samsung S24', '15000000', 'retail,online', 
            '1', '1', '1', '1', '', '', 'samsung,galaxy,s24,smartphone'
        ];
        $sheet->fromArray($sampleProduct, null, 'A2');

        // 2. Sheet Colors
        $colorSheet = $spreadsheet->createSheet();
        $colorSheet->setTitle('product_colors');
        $colorHeaders = ['product_id', 'color_name', 'color_code', 'status', 'creator', 'editor'];
        $colorSheet->fromArray($colorHeaders, null, 'A1');
        $sampleColor = ['Samsung Galaxy S24', 'Phantom Black', '#000000', '1', '', ''];
        $colorSheet->fromArray($sampleColor, null, 'A2');

        // 3. Sheet Variants
        $variantSheet = $spreadsheet->createSheet();
        $variantSheet->setTitle('product_variants');
        $variantHeaders = [
            'product_id', 'sku', 'variant_name', 'width', 'length', 'height', 
            'weight', 'price', 'stock_qty', 'min_order_qty', 'sort_order', 'status', 'creator', 'editor'
        ];
        $variantSheet->fromArray($variantHeaders, null, 'A1');
        $sampleVariant = [
            'Samsung Galaxy S24', 'SM-S921-256GB', '256GB / 8GB RAM', '7.06', '14.7', '0.76', 
            '167', '15000000', '50', '1', '1', '1', '', ''
        ];
        $variantSheet->fromArray($sampleVariant, null, 'A2');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'product_import_template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
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
        $headers = array_map(function($h) {
            return trim((string)$h);
        }, $headers);

        $grouped = [];
        $useId = in_array('product_id', array_map('strtolower', $headers));

        foreach ($rows as $row) {
            if (empty(array_filter($row))) {
                continue;
            }

            if (count($headers) !== count($row)) {
                if (count($row) < count($headers)) {
                    $row = array_pad($row, count($headers), null);
                } else {
                    $row = array_slice($row, 0, count($headers));
                }
            }

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
            return;
        }

        $product->colors()->delete();

        foreach ($rows as $color) {
            $colorData = [
                'color_name' => $color['color_name'] ?? null,
                'color_code' => $color['color_code'] ?? null,
                'status' => $this->toBoolean($color['status'] ?? true),
                'creator' => $color['creator'] ?? Auth::user()->name ?? 'admin',
                'editor' => $color['editor'] ?? Auth::user()->name ?? 'admin',
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
                'creator' => $variant['creator'] ?? Auth::user()->name ?? 'admin',
                'editor' => $variant['editor'] ?? Auth::user()->name ?? 'admin',
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
