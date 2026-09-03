<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;

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
    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand', 'images', 'variants']);

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

        $products = $query->latest()->paginate(15)->appends($request->query());
        
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        
        return view('pages.products.index', compact('products', 'categories', 'brands'));
    }

    public function create()
    {
        $product = new Product();
        $allProducts = Product::orderBy('name')->get();
        return view('pages.products.create', compact('product', 'allProducts'));
    }

    public function store(Request $request)
    {
        if (is_string($request->variants)) {
            $variantsData = json_decode($request->variants, true);
            if (is_array($variantsData)) {
                foreach ($variantsData as &$vData) {
                    foreach (['base_price', 'sell_price', 'stock_qty', 'min_order_qty', 'sort_order'] as $field) {
                        if (isset($vData[$field]) && trim((string)$vData[$field]) === '') {
                            $vData[$field] = null;
                        }
                    }
                }
            }
            $request->merge(['variants' => $variantsData]);
        }
        if (is_string($request->colors)) {
            $request->merge(['colors' => json_decode($request->colors, true)]);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug',
            'thumbnail' => 'nullable|string|max:255',
            'alt_text' => 'nullable|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'warranty_duration' => 'nullable|string|max:255',
            
            'segments' => 'nullable|array',
            'segments.*' => 'nullable|string|max:255',
            'best_seller' => 'boolean',
            'is_new' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean',
            'category_id' => 'nullable|string|exists:product_category,id',
            'brand_id' => 'nullable|string|exists:brands,id',
            'colors' => 'nullable|array',
            'colors.*.color_name' => 'nullable|string|max:255',
            'colors.*.color_code' => 'nullable|string|max:255',
            'colors.*.status' => 'boolean',
            'variants' => 'nullable|array',
            'variants.*.sku' => 'nullable|string|max:255',
            'variants.*.variant_name' => 'nullable|string|max:255',
            'variants.*.attributes' => 'nullable|array',
            'variants.*.base_price' => 'nullable|numeric|min:0',
            'variants.*.sell_price' => 'nullable|numeric|min:0',
            'variants.*.stock_qty' => 'nullable|integer|min:0',
            'variants.*.min_order_qty' => 'nullable|integer|min:0',
            'variants.*.sort_order' => 'nullable|integer|min:0',
            'variants.*.status' => 'boolean',
        ]);

        try {
            if ($request->hasFile('thumbnail_file')) {
                $path = $request->file('thumbnail_file')->store('product_images', 's3');
                if (!$path) {
                    throw new \Exception("Gagal mengupload thumbnail ke S3.");
                }
                $validated['thumbnail'] = $path;
            } elseif ($request->filled('thumbnail_file')) {
                $validated['thumbnail'] = $request->input('thumbnail_file');
            }

            $product = Product::create($validated);

            if (!empty($validated['thumbnail'])) {
                \Illuminate\Support\Facades\Log::channel('media')->info('Product thumbnail saved to S3 path', [
                    'product_id' => $product->id,
                    'thumbnail'  => $validated['thumbnail'],
                    'upload_mode' => $request->hasFile('thumbnail_file') ? 'server_s3_upload' : 'direct_s3_upload',
                ]);
            }

            if ($request->has('new_images')) {
                $newOrders = $request->input('new_image_orders');
                $newImages = $request->input('new_images', []) ?: $request->file('new_images', []);
                
                foreach ($newImages as $index => $fileOrPath) {
                    if ($fileOrPath instanceof \Illuminate\Http\UploadedFile) {
                        $path = $fileOrPath->store('product_images', 's3');
                        if (!$path) {
                            throw new \Exception("Gagal mengupload gambar tambahan ke S3.");
                        }
                    } else {
                        $path = $fileOrPath;
                    }
                    
                    if ($path) {
                        \App\Models\Product\Image::create([
                            'product_id' => $product->id,
                            'image' => $path,
                            'sort_order' => $newOrders[$index] ?? $index,
                            'status' => true,
                        ]);

                        \Illuminate\Support\Facades\Log::channel('media')->info('Product gallery image saved to S3 path', [
                            'product_id'  => $product->id,
                            'image_path'  => $path,
                            'sort_order'  => $newOrders[$index] ?? $index,
                            'upload_mode' => ($fileOrPath instanceof \Illuminate\Http\UploadedFile) ? 'server_s3_upload' : 'direct_s3_upload',
                        ]);
                    }
                }
            }

            if (isset($validated['colors']) && is_array($validated['colors'])) {
                foreach ($validated['colors'] as $colorData) {
                    Color::create(array_merge(['product_id' => $product->id], $colorData));
                }
            }

            if (isset($validated['variants']) && is_array($validated['variants'])) {
                foreach ($validated['variants'] as $variantData) {
                    // Map stock_qty from frontend to stock_quantity for database
                    if (array_key_exists('stock_qty', $variantData)) {
                        $variantData['stock_quantity'] = $variantData['stock_qty'];
                        unset($variantData['stock_qty']);
                    }
                    \App\Models\Product\Variant::create(array_merge(['product_id' => $product->id], $variantData));
                }
            }

            \Log::channel('product')->info("Product created successfully: " . $product->id);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product created successfully',
                    'redirect_url' => route('products.index'),
                ]);
            }

            return redirect()->route('products.index')->with('success', 'Product created successfully');
        } catch (\Exception $e) {
            \Log::channel('product')->error("Create Product Error: " . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan produk: ' . $e->getMessage(),
                ], 422);
            }

            return back()->withInput()->with('error', 'Gagal menyimpan produk: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $product = Product::with('variants', 'colors')->findOrFail($id);
        return view('pages.products.show', compact('product'));
    }

    public function edit($id)
    {
        $product = Product::with('colors', 'suggestedProducts')->findOrFail($id);
        $allProducts = Product::where('id', '!=', $id)->orderBy('name')->get();
        return view('pages.products.create', compact('product', 'allProducts'));
    }

    public function update(Request $request, $id)
    {
        if (is_string($request->variants)) {
            $variantsData = json_decode($request->variants, true);
            if (is_array($variantsData)) {
                foreach ($variantsData as &$vData) {
                    foreach (['base_price', 'sell_price', 'stock_qty', 'min_order_qty', 'sort_order'] as $field) {
                        if (isset($vData[$field]) && trim((string)$vData[$field]) === '') {
                            $vData[$field] = null;
                        }
                    }
                }
            }
            $request->merge(['variants' => $variantsData]);
        }
        if (is_string($request->colors)) {
            $request->merge(['colors' => json_decode($request->colors, true)]);
        }
        
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug,' . $product->id,
            'thumbnail' => 'nullable|string|max:255',
            'alt_text' => 'nullable|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'warranty_duration' => 'nullable|string|max:255',
            
            'segments' => 'nullable|array',
            'segments.*' => 'nullable|string|max:255',
            'best_seller' => 'boolean',
            'is_new' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean',
            'category_id' => 'nullable|string|exists:product_category,id',
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
            'variants.*.attributes' => 'nullable|array',
            'variants.*.base_price' => 'nullable|numeric|min:0',
            'variants.*.sell_price' => 'nullable|numeric|min:0',
            'variants.*.stock_qty' => 'nullable|integer|min:0',
            'variants.*.min_order_qty' => 'nullable|integer|min:0',
            'variants.*.sort_order' => 'nullable|integer|min:0',
            'variants.*.status' => 'boolean',
        ]);

        try {
            $oldThumbnail = $product->thumbnail;

            if ($request->hasFile('thumbnail_file')) {
                $path = $request->file('thumbnail_file')->store('product_images', 's3');
                if (!$path) {
                    throw new \Exception("Gagal mengupload thumbnail ke S3.");
                }
                $validated['thumbnail'] = $path;
            } elseif ($request->filled('thumbnail_file')) {
                $validated['thumbnail'] = $request->input('thumbnail_file');
            }

            // Unlink previous thumbnail from S3 if replaced
            if (!empty($validated['thumbnail']) && $oldThumbnail && $oldThumbnail !== $validated['thumbnail']) {
                unlink_media($oldThumbnail);
            }

            $product->update($validated);

            if (!empty($validated['thumbnail'])) {
                \Illuminate\Support\Facades\Log::channel('media')->info('Product thumbnail updated to S3 path', [
                    'product_id' => $product->id,
                    'thumbnail'  => $validated['thumbnail'],
                    'upload_mode' => $request->hasFile('thumbnail_file') ? 'server_s3_upload' : 'direct_s3_upload',
                ]);
            }

            // Handle Images
            if ($request->has('existing_images')) {
                $existingImages = $request->input('existing_images');
                $existingOrders = $request->input('existing_image_orders');
                foreach ($existingImages as $index => $imageId) {
                    \App\Models\Product\Image::where('id', $imageId)
                        ->where('product_id', $product->id)
                        ->update(['sort_order' => $existingOrders[$index] ?? $index]);
                }
                $removedImages = \App\Models\Product\Image::where('product_id', $product->id)
                    ->whereNotIn('id', $existingImages)
                    ->get();
                foreach ($removedImages as $removedImg) {
                    unlink_media($removedImg->image);
                    $removedImg->delete();
                }
            } else {
                $removedImages = \App\Models\Product\Image::where('product_id', $product->id)->get();
                foreach ($removedImages as $removedImg) {
                    unlink_media($removedImg->image);
                    $removedImg->delete();
                }
            }

            if ($request->has('new_images')) {
                $newOrders = $request->input('new_image_orders');
                $newImages = $request->input('new_images', []) ?: $request->file('new_images', []);
                
                foreach ($newImages as $index => $fileOrPath) {
                    if ($fileOrPath instanceof \Illuminate\Http\UploadedFile) {
                        $path = $fileOrPath->store('product_images', 's3');
                        if (!$path) {
                            throw new \Exception("Gagal mengupload gambar tambahan ke S3.");
                        }
                    } else {
                        $path = $fileOrPath;
                    }
                    
                    if ($path) {
                        \App\Models\Product\Image::create([
                            'product_id' => $product->id,
                            'image' => $path,
                            'sort_order' => $newOrders[$index] ?? $index,
                            'status' => true,
                        ]);

                        \Illuminate\Support\Facades\Log::channel('media')->info('Product gallery image added to S3 path', [
                            'product_id'  => $product->id,
                            'image_path'  => $path,
                            'sort_order'  => $newOrders[$index] ?? $index,
                            'upload_mode' => ($fileOrPath instanceof \Illuminate\Http\UploadedFile) ? 'server_s3_upload' : 'direct_s3_upload',
                        ]);
                    }
                }
            }

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
                $submittedVariantIds = [];
                foreach ($validated['variants'] as $variantData) {
                    // Map stock_qty from frontend to stock_quantity for database
                    if (array_key_exists('stock_qty', $variantData)) {
                        $variantData['stock_quantity'] = $variantData['stock_qty'];
                        unset($variantData['stock_qty']);
                    }
                    
                    if (isset($variantData['id'])) {
                        $submittedVariantIds[] = $variantData['id'];
                        $variant = \App\Models\Product\Variant::find($variantData['id']);
                        if ($variant) {
                            $variant->update($variantData);
                        }
                    } else {
                        \App\Models\Product\Variant::create(array_merge(['product_id' => $product->id], $variantData));
                    }
                }

                // Delete removed variants
                \App\Models\Product\Variant::where('product_id', $product->id)
                    ->whereNotIn('id', $submittedVariantIds)
                    ->delete();
            } else {
                \App\Models\Product\Variant::where('product_id', $product->id)->delete();
            }

            \Log::channel('product')->info("Product updated successfully: " . $product->id);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product updated successfully',
                    'redirect_url' => route('products.index'),
                ]);
            }

            return redirect()->route('products.index')->with('success', 'Product updated successfully');
        } catch (\Exception $e) {
            \Log::channel('product')->error("Update Product Error: " . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengupdate produk: ' . $e->getMessage(),
                ], 422);
            }

            return back()->withInput()->with('error', 'Gagal mengupdate produk: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $product = Product::with('images')->findOrFail($id);
        
        if ($product->thumbnail) {
            unlink_media($product->thumbnail);
        }

        foreach ($product->images as $img) {
            if ($img->image) {
                unlink_media($img->image);
            }
        }

        $product->delete();
        \Log::channel('product')->info("Product deleted successfully: " . $id);
        return redirect()->route('products.index')->with('success', 'Product deleted successfully');
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
            'product_id', 'sku', 'variant_name', 
            'price', 'stock_qty', 'min_order_qty', 'sort_order', 'status', 'creator', 'editor'
        ];
        $variantSheet->fromArray($variantHeaders, null, 'A1');
        $sampleVariant = [
            'product_id_here', 'VAR-001', 'King Size', '5000000', '10', '1', '1', '1', 'admin', 'admin'
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
}
