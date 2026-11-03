<?php

declare(strict_types=1);

namespace App\Http\Controllers\Jde;

use App\Http\Controllers\Controller;
use App\Models\Temporary\TemporaryJdeItemMaster;
use App\Models\Temporary\TemporaryJdeBasePrice;
use App\Models\Temporary\TemporaryJdeBranch;
use App\Models\Temporary\TemporaryJdeCustomer;
use App\Models\Product\Product;
use App\Models\Product\StoreProduct;
use App\Models\Product\Variant;
use App\Models\Product\Brand;
use App\Models\Product\Category;
use App\Services\JdePullService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JdeIntegrationController extends Controller
{
    public function __construct(
        protected JdePullService $jdePullService
    ) {}

    /**
     * Halaman Dashboard Integrasi JDE & Data Staging
     */
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'item_master'); // item_master, base_price, branch, customer
        $search = $request->query('search');
        $brand = $request->query('brand');

        // Daftar Brand Unik untuk filter
        $brands = TemporaryJdeItemMaster::query()
            ->whereNotNull('sales_code1_desc')
            ->whereRaw("TRIM(sales_code1_desc) != ''")
            ->where('sales_code1_desc', '!=', 'NON-BRAND')
            ->distinct()
            ->orderBy('sales_code1_desc')
            ->pluck('sales_code1_desc');

        // Statistik Ringkasan
        $stats = [
            'item_masters_total' => TemporaryJdeItemMaster::count(),
            'item_masters_pending' => TemporaryJdeItemMaster::where('sync_status', 'PENDING')->count(),
            'item_masters_branded' => TemporaryJdeItemMaster::whereNotNull('sales_code1_desc')
                ->whereRaw("TRIM(sales_code1_desc) != ''")
                ->where('sales_code1_desc', '!=', 'NON-BRAND')
                ->count(),
            'base_prices_total' => TemporaryJdeBasePrice::count(),
            'branches_total' => TemporaryJdeBranch::count(),
            'customers_total' => TemporaryJdeCustomer::count(),
            'last_sync_item' => TemporaryJdeItemMaster::latest('updated_at')->value('updated_at'),
            'last_sync_price' => TemporaryJdeBasePrice::latest('updated_at')->value('updated_at'),
            'last_sync_branch' => TemporaryJdeBranch::latest('updated_at')->value('updated_at'),
            'last_sync_customer' => TemporaryJdeCustomer::latest('updated_at')->value('updated_at'),
        ];

        $data = match ($tab) {
            'base_price' => $this->getBasePricesQuery($search),
            'branch' => $this->getBranchesQuery($search),
            'customer' => $this->getCustomersQuery($search),
            default => $this->getItemMastersQuery($search, $brand),
        };

        $user = auth('admin')->user();
        if ($user && method_exists($user, 'isSales') && $user->isSales()) {
            $availableStores = $user->assignedStores()->where('status', true)->where('deleted', false)->orderBy('name')->get(['store.id', 'store.name', 'store.code', 'store.address_number']);
        } elseif ($currentStore = session('current_store')) {
            $availableStores = \App\Models\Store\Store::where('id', $currentStore['id'])->get(['id', 'name', 'code', 'address_number']);
        } else {
            $availableStores = \App\Models\Store\Store::where('status', true)->where('deleted', false)->where('code', '!=', 'ONLINE_RETAIL')->orderBy('name')->get(['id', 'name', 'code', 'address_number']);
        }

        return view('pages.jde.index', compact('tab', 'stats', 'data', 'search', 'brand', 'brands', 'availableStores'));
    }

    /**
     * Trigger Manual Penarikan Data JDE (API / On-Demand)
     */
    public function pull(Request $request): JsonResponse
    {
        $type = $request->input('type', 'item_master');
        $limit = (int) $request->input('limit', 0);

        try {
            $result = match ($type) {
                'item_master' => $this->jdePullService->pullItemMasters(null, $limit),
                'base_price' => $this->jdePullService->pullBasePrices(null, $limit),
                'branch' => $this->jdePullService->pullBranches(null, $limit),
                'customer' => $this->jdePullService->pullCustomers(null, $limit),
                default => $this->jdePullService->pullAll($limit),
            };

            $isSuccess = $result['success'] ?? true;

            return response()->json([
                'success' => $isSuccess,
                'type' => $type,
                'data' => $result,
                'message' => $result['message'] ?? ($isSuccess ? 'Penarikan data JDE berhasil diproses.' : 'Gagal memproses penarikan data JDE.'),
            ], $isSuccess ? 200 : 400);
        } catch (\Throwable $e) {
            Log::channel('jde')->error('Manual JDE Pull Exception', [
                'type' => $type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menarik data JDE: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload File Dump JSON JDE secara manual
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:51200', // Max 50MB
            'type' => 'required|string|in:item_master,base_price,branch,customer',
        ]);

        $type = $request->input('type');
        $uploadedFile = $request->file('file');
        $path = $uploadedFile->storeAs('tmp/jde', $uploadedFile->getClientOriginalName());
        $fullPath = storage_path('app/' . $path);

        try {
            $result = match ($type) {
                'item_master' => $this->jdePullService->pullItemMasters($fullPath),
                'base_price' => $this->jdePullService->pullBasePrices($fullPath),
                'branch' => $this->jdePullService->pullBranches($fullPath),
                'customer' => $this->jdePullService->pullCustomers($fullPath),
            };

            return response()->json([
                'success' => true,
                'type' => $type,
                'data' => $result,
                'message' => "File {$uploadedFile->getClientOriginalName()} berhasil diproses: " . ($result['message'] ?? ''),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses file upload: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Salin satu item master JDE ke katalog produk CMS
     */
    public function copyItemMaster(Request $request, string $id): JsonResponse
    {
        $targetChannel = $request->input('target_channel', 'store_dealer'); // 'store_dealer' or 'web_retail'
        $item = TemporaryJdeItemMaster::findOrFail($id);

        $options = [
            'store_ids'         => $request->input('store_ids', []),
            'is_floor_display'  => $request->boolean('is_floor_display', true),
            'is_active_for_pos' => $request->boolean('is_active_for_pos', true),
        ];

        try {
            $user = auth('admin')->user();
            $userName = $user ? $user->name : 'Admin JDE';
            $userId = auth('admin')->id() ?? \App\Models\Admin::first()?->id;

            $result = DB::transaction(function () use ($item, $targetChannel, $userName, $userId, $options) {
                return $this->copySingleItemMaster($item, $targetChannel, $userName, $userId, $options);
            });

            $channelLabel = ($result['show_on_web'] ?? false) ? 'Online Retail (Web IMG)' : 'Toko Mebel / B2B Dealer';

            return response()->json([
                'success' => true,
                'message' => "Item [{$result['sku']}] {$result['product_name']} berhasil disalin ke katalog ({$channelLabel}).",
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            Log::channel('jde')->error('Error copying single JDE item master', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyalin item ke katalog: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Salin sekumpulan item master JDE (Batch Copy)
     */
    public function copyBatchItemMasters(Request $request): JsonResponse
    {
        $targetChannel = $request->input('target_channel', 'store_dealer');
        $brandFilter = $request->input('brand');
        $ids = $request->input('ids');
        $limit = min((int) $request->input('limit', 100), 200);

        $options = [
            'store_ids'         => $request->input('store_ids', []),
            'is_floor_display'  => $request->boolean('is_floor_display', true),
            'is_active_for_pos' => $request->boolean('is_active_for_pos', true),
        ];

        try {
            $user = auth('admin')->user();
            $userName = $user ? $user->name : 'Admin JDE';
            $userId = auth('admin')->id() ?? \App\Models\Admin::first()?->id;

            $query = TemporaryJdeItemMaster::query()->where('sync_status', 'PENDING');

            if (is_array($ids) && count($ids) > 0) {
                $query->whereIn('id', $ids);
            } elseif ($brandFilter === 'has_brand' || empty($brandFilter)) {
                $query->whereNotNull('sales_code1_desc')
                    ->whereRaw("TRIM(sales_code1_desc) != ''")
                    ->where('sales_code1_desc', '!=', 'NON-BRAND');
            } elseif ($brandFilter && $brandFilter !== 'no_brand') {
                $query->where('sales_code1_desc', $brandFilter);
            }

            $items = $query->limit($limit)->get();

            if ($items->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tidak ada item pending yang memenuhi kriteria untuk disalin.',
                    'copied_count' => 0,
                ]);
            }

            $copiedCount = 0;
            $errors = [];

            foreach ($items as $item) {
                try {
                    DB::transaction(function () use ($item, $targetChannel, $userName, $userId, $options) {
                        $this->copySingleItemMaster($item, $targetChannel, $userName, $userId, $options);
                    });
                    $copiedCount++;
                } catch (\Throwable $itemEx) {
                    $errors[] = [
                        'sku' => $item->second_item_number,
                        'error' => $itemEx->getMessage(),
                    ];
                }
            }

            $channelLabel = ($targetChannel === 'web_retail') ? 'Online Retail (Web IMG)' : 'Toko Mebel / B2B Dealer';

            return response()->json([
                'success' => true,
                'message' => "Berhasil menyalin {$copiedCount} item ke katalog ({$channelLabel})." . (count($errors) > 0 ? " (" . count($errors) . " gagal)" : ""),
                'copied_count' => $copiedCount,
                'error_count' => count($errors),
                'errors' => array_slice($errors, 0, 5),
            ]);
        } catch (\Throwable $e) {
            Log::channel('jde')->error('Error batch copying JDE item masters', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses batch copy: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Core worker to copy a single TemporaryJdeItemMaster into Product and Variant
     */
    protected function copySingleItemMaster(TemporaryJdeItemMaster $item, string $targetChannel, ?string $userName, ?string $userId = null, array $options = []): array
    {
        $showOnWeb = ($targetChannel === 'web_retail');
        $productName = trim((string) $item->description);
        if (empty($productName)) {
            $productName = 'Produk JDE ' . $item->second_item_number;
        }

        $brand = $this->resolveBrand($item->sales_code1_desc, $item->description);
        $category = $this->resolveCategory($item->description, $item->segments);

        // Base price lookup
        $basePrice = TemporaryJdeBasePrice::where('second_item_number', $item->second_item_number)
            ->orderByDesc('effective_date')
            ->value('base_price');

        $numericPrice = $basePrice !== null ? (float) $basePrice : 0.0;
        $sku = trim((string) $item->second_item_number);
        $variantName = trim((string) ($item->description_line_2 ?: '')) ?: $productName;

        // If Target is Store Dealer: Save to decoupled finish_goods.store_products!
        if (!$showOnWeb) {
            $storeIds = $options['store_ids'] ?? [];
            if (!empty($storeIds) && is_array($storeIds)) {
                $targetStores = \App\Models\Store\Store::whereIn('id', $storeIds)->where('deleted', false)->get();
            } elseif ($currentStore = session('current_store')) {
                $targetStores = \App\Models\Store\Store::where('id', $currentStore['id'])->get();
            } else {
                $user = auth('admin')->user();
                if ($user && method_exists($user, 'isSales') && $user->isSales()) {
                    $targetStores = $user->assignedStores()->where('status', true)->where('deleted', false)->get();
                } else {
                    $targetStores = \App\Models\Store\Store::where('status', true)->where('deleted', false)->where('code', '!=', 'ONLINE_RETAIL')->get();
                }
            }

            $isFloorDisplay = $options['is_floor_display'] ?? true;
            $isActiveForPos = $options['is_active_for_pos'] ?? true;

            $firstStoreProductId = null;
            foreach ($targetStores as $store) {
                $sp = StoreProduct::updateOrCreate(
                    ['store_id' => $store->id, 'sku' => $sku],
                    [
                        'temporary_item_id' => $item->id,
                        'short_item_no' => $item->short_item_no,
                        'item_name' => $productName,
                        'item_size' => $item->description_line_2,
                        'brand_name' => $brand?->name ?: $item->sales_code1_desc,
                        'category_name' => $category?->name ?: 'Matras',
                        'segments' => $item->segments,
                        'base_price' => $numericPrice,
                        'custom_retail_price' => $numericPrice,
                        'is_active_for_pos' => $isActiveForPos,
                        'is_floor_display' => $isFloorDisplay,
                    ]
                );
                if (!$firstStoreProductId) {
                    $firstStoreProductId = $sp->id;
                }
            }

            // Mark staging item as PROCESSED
            $item->update([
                'sync_status' => 'PROCESSED',
                'processed_product_id' => $firstStoreProductId,
                'processed_by' => $userId,
                'processed_at' => now(),
            ]);

            return [
                'item_id' => $item->id,
                'sku' => $sku,
                'product_id' => $firstStoreProductId,
                'product_name' => $productName,
                'variant_id' => $firstStoreProductId,
                'variant_name' => $variantName,
                'base_price' => $numericPrice,
                'show_on_web' => false,
            ];
        }

        // Otherwise, target is Web Retail E-Commerce: Save to finish_goods.products
        $product = Product::withoutGlobalScope('not-deleted')
            ->where('name', 'ilike', $productName)
            ->first();

        if (!$product) {
            $product = Product::create([
                'name' => $productName,
                'brand_id' => $brand?->id,
                'category_id' => $category?->id,
                'show_on_web' => true,
                'is_bundle' => false,
                'status' => 1,
                'short_description' => $item->description_line_2 ?: $productName,
                'description' => $productName . ($item->description_line_2 ? ' - ' . $item->description_line_2 : ''),
                'segments' => $item->segments,
                'courier_type' => 'keduanya',
                'creator' => $userName ?? 'JDE Sync',
                'deleted' => false,
            ]);
        } else {
            if ($product->deleted) {
                $product->deleted = false;
                $product->save();
            }
        }

        $variant = Variant::where('sku', $sku)->first();

        if (!$variant) {
            $variant = Variant::create([
                'product_id' => $product->id,
                'sku' => $sku,
                'variant_name' => $variantName,
                'attributes' => [
                    'size' => $item->description_line_2,
                    'template' => $item->template,
                    'segments' => $item->segments,
                    'short_item_no' => $item->short_item_no,
                ],
                'base_price' => $numericPrice,
                'sell_price' => $numericPrice,
                'stock_quantity' => 0,
                'min_order_qty' => 1,
                'status' => 1,
                'creator' => $userName ?? 'JDE Sync',
                'deleted' => false,
            ]);
        } else {
            if ($numericPrice > 0 && ((float)$variant->base_price <= 0 || (float)$variant->sell_price <= 0)) {
                $variant->base_price = $numericPrice;
                $variant->sell_price = $numericPrice;
            }
            $variant->deleted = false;
            $variant->save();
        }

        // Mark staging item as PROCESSED
        $item->update([
            'sync_status' => 'PROCESSED',
            'processed_product_id' => $product->id,
            'processed_by' => $userId,
            'processed_at' => now(),
        ]);

        return [
            'item_id' => $item->id,
            'sku' => $sku,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'variant_id' => $variant->id,
            'variant_name' => $variant->variant_name,
            'base_price' => $numericPrice,
            'show_on_web' => true,
        ];
    }

    protected function resolveBrand(?string $brandName, ?string $description): ?Brand
    {
        $cleanBrand = strtoupper(trim((string) $brandName));
        if (!empty($cleanBrand) && $cleanBrand !== 'NON-BRAND') {
            $found = Brand::withoutGlobalScope('not-deleted')->whereRaw('UPPER(name) = ?', [$cleanBrand])->first();
            if ($found) {
                return $found;
            }
        }

        $desc = strtoupper((string) $description);
        $candidates = ['LADY', 'ELITE', 'SERENITY', 'ROYAL', 'TOTE', 'CLASSIC', 'MORO'];
        foreach ($candidates as $cand) {
            if (str_contains($desc, $cand)) {
                $found = Brand::withoutGlobalScope('not-deleted')->whereRaw('UPPER(name) = ?', [$cand])->first();
                if ($found) {
                    return $found;
                }
            }
        }

        return Brand::withoutGlobalScope('not-deleted')->whereRaw('UPPER(name) = ?', ['ROYAL'])->first()
            ?: Brand::withoutGlobalScope('not-deleted')->first();
    }

    protected function resolveCategory(?string $description, ?array $segments): ?Category
    {
        $desc = strtoupper((string) $description);
        $seg1 = strtoupper((string) ($segments['segment1'] ?? ''));
        $seg2 = strtoupper((string) ($segments['segment2'] ?? ''));
        $fullCheck = $desc . ' ' . $seg1 . ' ' . $seg2;

        if (str_contains($fullCheck, 'DIVAN') || str_contains($fullCheck, 'DV ')) {
            $cat = Category::where('slug', 'divan')->first();
            if ($cat) return $cat;
        }

        if (str_contains($fullCheck, 'HEADBOARD') || str_contains($fullCheck, 'HB ')) {
            $cat = Category::where('slug', 'headboard')->first();
            if ($cat) return $cat;
        }

        if (str_contains($fullCheck, 'SOFA')) {
            $cat = Category::where('slug', 'sofa-bed')->first();
            if ($cat) return $cat;
        }

        if (str_contains($fullCheck, 'TOPPER')) {
            $cat = Category::where('slug', 'topper')->first();
            if ($cat) return $cat;
        }

        if (str_contains($fullCheck, 'PROTECTOR')) {
            $cat = Category::where('slug', 'mattress-protector')->first();
            if ($cat) return $cat;
        }

        if (str_contains($fullCheck, 'BUSA') || str_contains($fullCheck, 'FOAM')) {
            $cat = Category::where('slug', 'foam-mattress')->first();
            if ($cat) return $cat;
        }

        if (str_contains($fullCheck, 'SPRING')) {
            $cat = Category::where('slug', 'spring-mattress')->first();
            if ($cat) return $cat;
        }

        if (str_contains($fullCheck, 'MULTIBED')) {
            $cat = Category::where('slug', 'multibed')->first();
            if ($cat) return $cat;
        }

        return Category::where('slug', 'mattress')->first() ?: Category::first();
    }

    protected function getItemMastersQuery(?string $search, ?string $brand = null)
    {
        $query = TemporaryJdeItemMaster::query();

        // Filter Brand
        if ($brand === 'has_brand') {
            $query->whereNotNull('sales_code1_desc')
                ->whereRaw("TRIM(sales_code1_desc) != ''")
                ->where('sales_code1_desc', '!=', 'NON-BRAND');
        } elseif ($brand === 'no_brand') {
            $query->where(function ($q) {
                $q->whereNull('sales_code1_desc')
                  ->orWhereRaw("TRIM(sales_code1_desc) = ''")
                  ->orWhere('sales_code1_desc', 'NON-BRAND');
            });
        } elseif (!empty($brand)) {
            $query->where('sales_code1_desc', $brand);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('second_item_number', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%")
                  ->orWhere('sales_code1_desc', 'ilike', "%{$search}%");
            });
        }

        // Urutkan data dari yang memiliki brand terlebih dahulu, kemudian alfabetis brand & SKU
        $query->orderByRaw("
            CASE 
                WHEN sales_code1_desc IS NOT NULL AND TRIM(sales_code1_desc) != '' AND sales_code1_desc != 'NON-BRAND' THEN 0 
                ELSE 1 
            END ASC
        ")
        ->orderBy('sales_code1_desc', 'asc')
        ->orderBy('second_item_number', 'asc');

        return $query->paginate(15)->withQueryString();
    }

    protected function getBasePricesQuery(?string $search)
    {
        $query = TemporaryJdeBasePrice::query()->latest('updated_at');
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('second_item_number', 'ilike', "%{$search}%")
                  ->orWhere('business_unit', 'ilike', "%{$search}%")
                  ->orWhere('business_unit_desc', 'ilike', "%{$search}%");
            });
        }
        return $query->paginate(15)->withQueryString();
    }

    protected function getBranchesQuery(?string $search)
    {
        $query = TemporaryJdeBranch::query()->latest('updated_at');
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('business_unit', 'ilike', "%{$search}%")
                  ->orWhere('cabang_desc', 'ilike', "%{$search}%")
                  ->orWhere('cabang_code', 'ilike', "%{$search}%");
            });
        }
        return $query->paginate(15)->withQueryString();
    }

    protected function getCustomersQuery(?string $search)
    {
        $query = TemporaryJdeCustomer::query()->latest('updated_at');
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('alpha_name', 'ilike', "%{$search}%")
                  ->orWhere('business_unit', 'ilike', "%{$search}%")
                  ->orWhere('cabang_desc', 'ilike', "%{$search}%")
                  ->orWhere('address_number', 'like', "%{$search}%");
            });
        }
        return $query->paginate(15)->withQueryString();
    }
}
