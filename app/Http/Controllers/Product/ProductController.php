<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;

use App\Models\Product\Product;
use App\Models\Product\StoreProduct;
use App\Models\Product\Color;
use App\Models\Product\Brand;
use App\Models\Product\Category;
use App\Models\Product\Variant;
use App\Models\Product\Tag;
use App\Models\Product\ProductTag;
use App\Models\Product\TagRelation;
use App\Models\Promo\PriceProductSetting;
use App\Models\Store\Store;
use App\Models\Store\SalesStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $isPrivilegedUser = $admin && ($admin->isSuperAdmin() || $admin->user_type === 'admin' || $admin->user_type === 'sales' || 
            (method_exists($admin, 'hasRole') && ($admin->hasRole('Admin') || $admin->hasRole('Sales Canvasser'))));

        // Mode Tampilan: Untuk Sales & Admin, default langsung ke Online Store (Web IMG) kecuali ada request view=store atau store_id
        $viewMode = $request->query('view', $isPrivilegedUser ? 'online' : 'store');
        $explicitStoreId = $request->query('store_id');
        $currentStore = session('current_store');

        $isStoreView = false;
        $availableStores = collect();

        if ($isPrivilegedUser) {
            // Siapkan daftar toko yang relevan untuk switch tampilan jika diinginkan
            if ($admin->user_type === 'sales' || (method_exists($admin, 'hasRole') && $admin->hasRole('Sales Canvasser'))) {
                $assignedIds = SalesStore::where('sales_user_id', $admin->id)->pluck('store_id');
                $availableStores = Store::whereIn('id', $assignedIds)->where('deleted', false)->where('status', true)->orderBy('name')->get();
            } else {
                $availableStores = Store::where('deleted', false)->where('status', true)->orderBy('name')->take(50)->get();
            }

            if ($viewMode === 'store' || !empty($explicitStoreId)) {
                $isStoreView = true;
                if (!empty($explicitStoreId)) {
                    $st = Store::find($explicitStoreId);
                    if ($st) {
                        $currentStore = [
                            'id' => $st->id,
                            'name' => $st->name,
                            'address_number' => (int) $st->address_number,
                            'code' => $st->code,
                        ];
                    }
                } elseif (empty($currentStore) && $availableStores->isNotEmpty()) {
                    $firstStore = $availableStores->first();
                    $currentStore = [
                        'id' => $firstStore->id,
                        'name' => $firstStore->name,
                        'address_number' => (int) $firstStore->address_number,
                        'code' => $firstStore->code,
                    ];
                }
            } else {
                // Default: Langsung ke Online Store (Web Retail Catalog)
                $isStoreView = false;
            }
        } else {
            // Toko Partner / SPG: Selalu ke katalog toko mereka
            $isStoreView = true;
        }

        if ($isStoreView && !empty($currentStore)) {
            $query = StoreProduct::with(['variant.product.images'])->where('store_id', $currentStore['id']);

            if ($request->filled('search')) {
                $search = trim($request->search);
                $query->where(function ($q) use ($search) {
                    $q->where('item_name', 'ilike', "%{$search}%")
                      ->orWhere('sku', 'ilike', "%{$search}%")
                      ->orWhere('brand_name', 'ilike', "%{$search}%")
                      ->orWhere('category_name', 'ilike', "%{$search}%");
                });
            }

            if ($brandId = $request->query('brand_id')) {
                $brandObj = Brand::find($brandId);
                if ($brandObj) {
                    $query->where('brand_name', 'ilike', "%{$brandObj->name}%");
                }
            }

            if ($categoryId = $request->query('category_id')) {
                $catObj = Category::find($categoryId);
                if ($catObj) {
                    $query->where('category_name', 'ilike', "%{$catObj->name}%");
                }
            }

            if ($tagId = $request->query('tag_id')) {
                $matchingSkus = Variant::whereHas('product.tags', function ($q) use ($tagId) {
                    $q->where('product_tags.id', $tagId);
                })->pluck('sku');
                $query->whereIn('sku', $matchingSkus);
            }

            $products = $query->orderBy('display_order')->orderBy('item_name')->paginate(15)->appends($request->query());

            // Resolusi Promo Toko Terisolasi:
            // Promo B2B Dealer BUKAN promo global, melainkan spesifik per Toko, per Tier Toko, atau per Channel Group Toko.
            $storeModel = Store::with('tier')->find($currentStore['id']);
            $currentStore['tier_id'] = $storeModel?->tier_id;
            $currentStore['tier_name'] = $storeModel?->tier?->name;

            // 1. Prioritas Utama: Promo Eksklusif Toko Ini (scope_store_type = 2 atau pivot price_product_setting_store)
            $activePromo = PriceProductSetting::withoutGlobalScope('active')
                ->where('is_active', true)
                ->where('deleted', false)
                ->where(function ($q) {
                    $q->whereNull('start_date')->orWhere('start_date', '<=', now());
                })
                ->where(function ($q) {
                    $q->whereNull('end_date')->orWhere('end_date', '>=', now());
                })
                ->where(function ($q) use ($storeModel) {
                    $q->where(function ($q2) use ($storeModel) {
                        $q2->where('scope_store_type', 2)->where('scope_store_id', $storeModel?->id);
                    })
                    ->orWhereExists(function ($subQ) use ($storeModel) {
                        $subQ->select(DB::raw(1))
                             ->from('promo.price_product_setting_store')
                             ->whereColumn('price_product_setting_store.price_product_setting_id', 'price_product_settings.id')
                             ->where('price_product_setting_store.store_id', $storeModel?->id)
                             ->where('price_product_setting_store.deleted', false);
                    });
                })
                ->orderBy('sort_order')
                ->first();

            // 2. Prioritas Kedua: Promo Berdasarkan Tier Toko (scope_store_type = 1, misal Tier Platinum/Gold)
            if (!$activePromo && $storeModel?->tier_id) {
                $activePromo = PriceProductSetting::withoutGlobalScope('active')
                    ->where('is_active', true)
                    ->where('deleted', false)
                    ->where(function ($q) {
                        $q->whereNull('start_date')->orWhere('start_date', '<=', now());
                    })
                    ->where(function ($q) {
                        $q->whereNull('end_date')->orWhere('end_date', '>=', now());
                    })
                    ->where('scope_store_type', 1)
                    ->where('scope_store_id', $storeModel->tier_id)
                    ->orderBy('sort_order')
                    ->first();
            }

            // 3. Prioritas Ketiga: Promo Channel Group Toko (scope_store_type = 3)
            if (!$activePromo && $storeModel?->store_group_id) {
                $activePromo = PriceProductSetting::withoutGlobalScope('active')
                    ->where('is_active', true)
                    ->where('deleted', false)
                    ->where(function ($q) {
                        $q->whereNull('start_date')->orWhere('start_date', '<=', now());
                    })
                    ->where(function ($q) {
                        $q->whereNull('end_date')->orWhere('end_date', '>=', now());
                    })
                    ->where('scope_store_type', 3)
                    ->where('scope_store_id', $storeModel->store_group_id)
                    ->orderBy('sort_order')
                    ->first();
            }

            // Ambil daftar item yang masuk dalam promo jika promo ber-scope tertentu (scope = 2)
            $promoItemVariantIds = collect();
            $promoItemProductIds = collect();
            if ($activePromo && (int)$activePromo->scope === 2) {
                $promoItems = DB::table('promo.price_product_setting_items')
                    ->where('price_product_setting_id', $activePromo->id)
                    ->where('deleted', false)
                    ->get();
                $promoItemVariantIds = $promoItems->pluck('variant_id')->filter();
                $promoItemProductIds = $promoItems->pluck('product_id')->filter();

                // Periksa juga item di price_product_setting_store
                $storePricingItems = DB::table('promo.price_product_setting_store')
                    ->where('price_product_setting_id', $activePromo->id)
                    ->where('store_id', $currentStore['id'])
                    ->where('deleted', false)
                    ->get();
                $promoItemVariantIds = $promoItemVariantIds->merge($storePricingItems->pluck('variant_id')->filter());
                $promoItemProductIds = $promoItemProductIds->merge($storePricingItems->pluck('product_id')->filter());
            }

            // Ambil semua StorePricing khusus toko ini
            $storePricings = \App\Models\Promo\StorePricing::with('priceProductSetting')
                ->where('store_id', $currentStore['id'])
                ->where('deleted', false)
                ->get();
            $storePricingByVariant = $storePricings->whereNotNull('variant_id')->keyBy('variant_id');
            $storePricingByProduct = $storePricings->whereNotNull('product_id')->keyBy('product_id');

            foreach ($products as $prod) {
                $sellingPrice = (float) ($prod->custom_retail_price > 0 
                    ? $prod->custom_retail_price 
                    : ($prod->suggested_retail_price > 0 ? $prod->suggested_retail_price : $prod->base_price));
                $prod->selling_price = $sellingPrice;

                $variantId = $prod->variant?->id;
                $productId = $prod->variant?->product_id;

                // 1. Cek apakah ada StorePricing spesifik (Rowset Adjustments) untuk produk/varian ini di toko ini
                $specificStorePricing = ($variantId && $storePricingByVariant->has($variantId)) 
                    ? $storePricingByVariant->get($variantId) 
                    : (($productId && $storePricingByProduct->has($productId)) ? $storePricingByProduct->get($productId) : null);

                if ($specificStorePricing) {
                    $baseAdjPrice = $specificStorePricing->base_price > 0 ? $specificStorePricing->base_price : $sellingPrice;
                    $finalAdjPrice = $specificStorePricing->final_price;
                    $prod->selling_price = $baseAdjPrice;

                    if ($finalAdjPrice < $baseAdjPrice) {
                        $prod->active_promo = $specificStorePricing->priceProductSetting ?: $activePromo;
                        $prod->promo_discount = round($baseAdjPrice - $finalAdjPrice, 2);
                        $prod->promo_price = $finalAdjPrice;
                    } else {
                        // Jika final price sama dengan base price namun memiliki program promo terhubung yang aktif
                        $linkedPromo = $specificStorePricing->priceProductSetting;
                        $calculatedDiscount = 0;
                        if ($linkedPromo && $linkedPromo->is_active && !$linkedPromo->deleted) {
                            if ((int)$linkedPromo->discount_type === 1) {
                                $calculatedDiscount = round($baseAdjPrice * ((float)$linkedPromo->discount_value / 100), 2);
                            } elseif ((int)$linkedPromo->discount_type === 2) {
                                $calculatedDiscount = min($baseAdjPrice, (float)$linkedPromo->discount_value);
                            }
                        }

                        if ($calculatedDiscount > 0) {
                            $prod->active_promo = $linkedPromo;
                            $prod->promo_discount = $calculatedDiscount;
                            $prod->promo_price = max(0, $baseAdjPrice - $calculatedDiscount);
                        } else {
                            $prod->active_promo = null;
                            $prod->promo_discount = 0;
                            $prod->promo_price = $finalAdjPrice;
                        }
                    }
                    continue;
                }

                // 2. Evaluasi promo toko tingkat header ($activePromo)
                $isEligibleForPromo = false;
                if ($activePromo) {
                    if ((int)$activePromo->scope === 1) {
                        // Scope 1: Berlaku untuk seluruh produk di toko ini
                        $isEligibleForPromo = true;
                    } elseif ((int)$activePromo->scope === 2) {
                        // Scope 2: Berlaku untuk produk / varian tertentu yang dipilih
                        if (($variantId && $promoItemVariantIds->contains($variantId)) ||
                            ($productId && $promoItemProductIds->contains($productId))) {
                            $isEligibleForPromo = true;
                        }
                    }
                }

                if ($isEligibleForPromo) {
                    $discount = 0;
                    if ((int)$activePromo->discount_type === 1) {
                        $discount = round($sellingPrice * ((float)$activePromo->discount_value / 100), 2);
                    } elseif ((int)$activePromo->discount_type === 2) {
                        $discount = min($sellingPrice, (float)$activePromo->discount_value);
                    }
                    $prod->active_promo = $activePromo;
                    $prod->promo_discount = $discount;
                    $prod->promo_price = max(0, $sellingPrice - $discount);
                } else {
                    $prod->active_promo = null;
                    $prod->promo_discount = 0;
                    $prod->promo_price = $sellingPrice;
                }
            }

            $categories = Category::orderBy('name')->get();
            $brands = Brand::orderBy('name')->get();
            $tags = \App\Models\Product\ProductTag::where('deleted', false)->orderBy('name')->get();

            return view('pages.products.index', compact('products', 'categories', 'brands', 'tags', 'isStoreView', 'currentStore', 'activePromo', 'isPrivilegedUser', 'availableStores', 'viewMode'));
        }

        // Web IMG products
        $query = Product::with(['category', 'brand', 'images', 'variants'])
            ->where('show_on_web', true);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $tokens = array_filter(preg_split('/\s+/', $search));
            $query->where(function ($q) use ($search, $tokens) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('slug', 'ilike', "%{$search}%")
                  ->orWhere('code', 'ilike', "%{$search}%")
                  ->orWhereHas('variants', function ($q2) use ($search) {
                      $q2->where('sku', 'ilike', "%{$search}%")
                         ->orWhere('variant_name', 'ilike', "%{$search}%");
                  });

                if (count($tokens) > 1) {
                    $q->orWhere(function ($qSub) use ($tokens) {
                        foreach ($tokens as $token) {
                            $qSub->where(function ($tokQ) use ($token) {
                                $tokQ->where('name', 'ilike', "%{$token}%")
                                     ->orWhere('slug', 'ilike', "%{$token}%")
                                     ->orWhere('code', 'ilike', "%{$token}%")
                                     ->orWhereHas('variants', function ($vq) use ($token) {
                                         $vq->where('sku', 'ilike', "%{$token}%")
                                            ->orWhere('variant_name', 'ilike', "%{$token}%");
                                     });
                            });
                        }
                    });
                }
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

        if ($tagId = $request->query('tag_id')) {
            $query->whereHas('tags', function ($q) use ($tagId) {
                $q->where('product_tags.id', $tagId);
            });
        }

        $products = $query->latest()->paginate(15)->appends($request->query());
        
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $tags = \App\Models\Product\ProductTag::where('deleted', false)->orderBy('name')->get();
        
        return view('pages.products.index', compact('products', 'categories', 'brands', 'tags', 'isStoreView', 'currentStore', 'isPrivilegedUser', 'availableStores', 'viewMode'));
    }

    public function autocomplete(Request $request)
    {
        $q = trim((string) $request->input('q', $request->input('search', '')));
        $view = $request->input('view', 'online');
        $storeId = $request->input('store_id');

        if (strlen($q) < 1) {
            return response()->json([]);
        }

        $results = [];

        if ($view === 'store') {
            $query = StoreProduct::query();
            if (!empty($storeId)) {
                $query->where('store_id', $storeId);
            } elseif ($currentStore = session('current_store')) {
                $query->where('store_id', $currentStore['id']);
            }

            $items = $query->where(function($sub) use ($q) {
                $sub->where('item_name', 'ilike', "%{$q}%")
                    ->orWhere('sku', 'ilike', "%{$q}%")
                    ->orWhere('brand_name', 'ilike', "%{$q}%")
                    ->orWhere('category_name', 'ilike', "%{$q}%");
            })
            ->orderBy('item_name')
            ->limit(8)
            ->get();

            foreach ($items as $item) {
                $price = (float) ($item->custom_retail_price > 0 ? $item->custom_retail_price : ($item->suggested_retail_price > 0 ? $item->suggested_retail_price : $item->base_price));
                $results[] = [
                    'id'               => $item->id,
                    'sku'              => $item->sku,
                    'name'             => $item->item_name,
                    'brand'            => $item->brand_name ?? 'Royal',
                    'category'         => $item->category_name ?? 'Kasur',
                    'price_label'      => 'Rp ' . number_format($price, 0, ',', '.'),
                    'is_floor_display' => (bool) $item->is_floor_display,
                    'is_active_pos'    => (bool) $item->is_active_for_pos,
                    'type'             => 'store',
                ];
            }
        } else {
            $items = Product::with(['brand', 'category', 'images'])
                ->where('show_on_web', true)
                ->where(function($sub) use ($q) {
                    $sub->where('name', 'ilike', "%{$q}%")
                        ->orWhere('slug', 'ilike', "%{$q}%")
                        ->orWhere('code', 'ilike', "%{$q}%")
                        ->orWhereHas('brand', function($b) use ($q) {
                            $b->where('name', 'ilike', "%{$q}%");
                        })
                        ->orWhereHas('category', function($c) use ($q) {
                            $c->where('name', 'ilike', "%{$q}%");
                        });
                })
                ->orderBy('name')
                ->limit(8)
                ->get();

            foreach ($items as $item) {
                $img = $item->images->first()?->image_url;
                $results[] = [
                    'id'          => $item->id,
                    'sku'         => $item->code ?? $item->sku ?? '-',
                    'name'        => $item->name,
                    'brand'       => $item->brand?->name ?? 'Royal',
                    'category'    => $item->category?->name ?? 'Kasur Retail',
                    'price_label' => 'Rp ' . number_format((float)$item->price, 0, ',', '.'),
                    'image'       => $img,
                    'type'        => 'online',
                ];
            }
        }

        return response()->json($results);
    }

    public function storesAutocomplete(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) {
            return response()->json([]);
        }

        $q = trim((string) $request->input('q', $request->input('search', '')));
        $query = Store::with('tier')->where('deleted', false)->where('status', true);

        // Scoping toko jika login sebagai Sales Canvasser
        if ($admin->user_type === 'sales' || (method_exists($admin, 'hasRole') && $admin->hasRole('Sales Canvasser'))) {
            $assignedIds = SalesStore::where('sales_user_id', $admin->id)->pluck('store_id');
            $query->whereIn('id', $assignedIds);
        }

        if (strlen($q) > 0) {
            $isNum = is_numeric($q);
            $query->where(function ($sub) use ($q, $isNum) {
                $sub->where('name', 'ilike', "%{$q}%")
                    ->orWhere('code', 'ilike', "%{$q}%");
                if ($isNum) {
                    $sub->orWhere('address_number', (int) $q);
                }
            });
        }

        $stores = $query->orderBy('name')->limit(15)->get();

        $list = [];
        $webMatches = empty($q) || str_contains(strtolower('Web Official Store (Online Retail IMG Pusat)'), strtolower($q))
            || str_contains(strtolower($q), 'web') || str_contains(strtolower($q), 'online') || str_contains(strtolower($q), 'pusat');

        if ($webMatches) {
            $list[] = [
                'id'             => 'online',
                'name'           => 'Web Official Store (Online Retail IMG Pusat)',
                'address_number' => null,
                'code'           => 'ONLINE_D2C',
                'tier_name'      => 'D2C Retail',
                'phone'          => 'Pusat',
                'address'        => 'Katalog Publik E-Commerce Official',
                'is_online'      => true,
            ];
        }

        foreach ($stores as $st) {
            $list[] = [
                'id'             => $st->id,
                'name'           => $st->name,
                'address_number' => $st->address_number ? (int) $st->address_number : null,
                'code'           => $st->code,
                'tier_name'      => $st->tier?->name ?? 'Standard',
                'phone'          => $st->phone ?? '-',
                'address'        => $st->address ?? '',
                'is_online'      => false,
            ];
        }

        return response()->json($list);
    }

    public function create()
    {
        $product = new Product();
        $allProducts = Product::orderBy('name')->get();
        $tags = ProductTag::where('deleted', false)->orderBy('name')->get();
        $selectedTagIds = [];
        return view('pages.products.create', compact('product', 'allProducts', 'tags', 'selectedTagIds'));
    }

    public function store(Request $request)
    {
        if (is_string($request->variants)) {
            $variantsData = json_decode($request->variants, true);
            if (is_array($variantsData)) {
                foreach ($variantsData as &$vData) {
                    foreach (['base_price', 'sell_price', 'stock_qty', 'min_order_qty', 'sort_order', 'length', 'width', 'height', 'weight', 'package_length', 'package_width', 'package_height', 'package_weight'] as $field) {
                        if (isset($vData[$field]) && trim((string)$vData[$field]) === '') {
                            $vData[$field] = null;
                        }
                    }
                    if (isset($vData['price']) && (!isset($vData['sell_price']) || $vData['sell_price'] === null)) {
                        $vData['sell_price'] = $vData['price'];
                    }
                    if (!isset($vData['sell_price']) || $vData['sell_price'] === null) {
                        $vData['sell_price'] = isset($vData['base_price']) && $vData['base_price'] !== null ? (float)$vData['base_price'] : 0;
                    }
                    if (!isset($vData['base_price']) || $vData['base_price'] === null) {
                        $vData['base_price'] = isset($vData['sell_price']) && $vData['sell_price'] !== null ? (float)$vData['sell_price'] : 0;
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
            'code' => 'nullable|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'slug')->where(fn ($q) => $q->where('deleted', false)),
            ],
            'thumbnail' => 'nullable|string|max:255',
            'alt_text' => 'nullable|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'category_id' => 'required|string|exists:product_category,id',
            'brand_id' => 'required|string|exists:brands,id',
            'warranty_duration' => 'nullable|string|max:255',
            'courier_type' => 'nullable|string|in:toko,ekspedisi,expedisi,keduanya',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'best_seller' => 'boolean',
            'is_new' => 'boolean',
            'show_on_web' => 'boolean',
            'sort_order' => 'integer|min:0',
            'status' => 'boolean',
            'suggested_products' => 'nullable|array',
            'suggested_products.*' => 'exists:products,id',
            'tags' => 'nullable|array',
            'tags.*' => 'nullable|string|exists:product_tags,id',
            'colors' => 'nullable|array',
            'colors.*.color_name' => 'nullable|string|max:255',
            'colors.*.color_code' => 'nullable|string|max:255',
            'colors.*.status' => 'boolean',
            'variants' => 'nullable|array',
            'variants.*.sku' => 'nullable|string|max:255',
            'variants.*.variant_name' => 'nullable|string|max:255',
            'variants.*.attributes' => 'nullable|array',
            'variants.*.image' => 'nullable|string|max:500',
            'variants.*.base_price' => 'nullable|numeric|min:0',
            'variants.*.sell_price' => 'nullable|numeric|min:0',
            'variants.*.length' => 'nullable|numeric|min:0',
            'variants.*.width' => 'nullable|numeric|min:0',
            'variants.*.height' => 'nullable|numeric|min:0',
            'variants.*.weight' => 'nullable|numeric|min:0',
            'variants.*.package_length' => 'nullable|numeric|min:0',
            'variants.*.package_width' => 'nullable|numeric|min:0',
            'variants.*.package_height' => 'nullable|numeric|min:0',
            'variants.*.package_weight' => 'nullable|numeric|min:0',
            'variants.*.stock_qty' => 'nullable|integer|min:0',
            'variants.*.min_order_qty' => 'nullable|integer|min:0',
            'variants.*.sort_order' => 'nullable|integer|min:0',
        ], [
            'name.required' => 'Nama produk wajib diisi.',
            'category_id.required' => 'Kategori produk wajib dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',
            'brand_id.required' => 'Brand wajib dipilih.',
            'brand_id.exists' => 'Brand yang dipilih tidak valid.',
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

            $validated['courier_type'] = $validated['courier_type'] ?? 'keduanya';
            $validated['show_on_web'] = $request->has('show_on_web') ? (bool)$request->input('show_on_web') : true;
            $product = Product::create($validated);

            if ($request->has('tags')) {
                $tagIds = is_array($request->tags) ? array_filter($request->tags) : [];
                foreach ($tagIds as $tagId) {
                    TagRelation::create([
                        'product_id' => $product->id,
                        'tag_id' => $tagId,
                    ]);
                }
            }

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
                    unset($colorData['status'], $colorData['id']);
                    Color::create(array_merge(['product_id' => $product->id], $colorData));
                }
            }

            if (isset($validated['variants']) && is_array($validated['variants'])) {
                foreach ($validated['variants'] as $idx => $variantData) {
                    $vImage = $variantData['image'] ?? null;
                    unset($variantData['image'], $variantData['id']);

                    // Map stock_qty from frontend to stock_quantity for database
                    if (array_key_exists('stock_qty', $variantData)) {
                        $variantData['stock_quantity'] = $variantData['stock_qty'];
                        unset($variantData['stock_qty']);
                    }

                    // Keep attributes clean for pos-dealer-web (only variant options, no image key)
                    if (isset($variantData['attributes']) && is_array($variantData['attributes'])) {
                        unset($variantData['attributes']['image'], $variantData['attributes']['image_url']);
                    }

                    // Fallback to product dimensions if variant not specified
                    foreach (['length', 'width', 'height', 'weight'] as $dim) {
                        if (!isset($variantData[$dim]) || $variantData[$dim] === '' || $variantData[$dim] === null) {
                            $variantData[$dim] = $validated[$dim] ?? null;
                        }
                    }

                    $variant = \App\Models\Product\Variant::create(array_merge(['product_id' => $product->id], $variantData));
                    \App\Services\InventoryService::ensureVariantInventory($product->id, $variant->id);

                    if ($vImage) {
                        \App\Models\Product\Image::create([
                            'product_id' => $product->id,
                            'variant_id' => $variant->id,
                            'image' => $vImage,
                            'sort_order' => $idx,
                            'status' => true,
                        ]);
                    }
                }
            }

            \Log::channel('product')->info("Product created successfully: " . $product->id);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Produk berhasil ditambahkan.',
                    'redirect_url' => route('products.show', $product->id),
                ]);
            }

            return redirect()->route('products.show', $product->id)->with('success', 'Produk berhasil ditambahkan.');
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
        $product = Product::with(['variants', 'colors', 'category', 'brand', 'images', 'suggestedProducts', 'tags'])->findOrFail($id);
        return view('pages.products.show', compact('product'));
    }

    public function edit($id)
    {
        $product = Product::with(['variants' => function ($q) {
            $q->where('deleted', false);
        }, 'colors', 'suggestedProducts', 'images', 'tags'])->findOrFail($id);
        
        $variantOrderCounts = DB::table('order_items')
            ->whereIn('product_variant_id', $product->variants->pluck('id'))
            ->groupBy('product_variant_id')
            ->selectRaw('product_variant_id, count(*) as count')
            ->pluck('count', 'product_variant_id')
            ->toArray();
        
        foreach ($product->variants as $v) {
            $v->has_orders = ($variantOrderCounts[$v->id] ?? 0) > 0;
        }

        $allProducts = Product::where('id', '!=', $id)->orderBy('name')->get();
        $tags = ProductTag::where('deleted', false)->orderBy('name')->get();
        $selectedTagIds = $product->tags->pluck('id')->toArray();
        return view('pages.products.create', compact('product', 'allProducts', 'tags', 'selectedTagIds'));
    }

    public function update(Request $request, $id)
    {
        if (is_string($request->variants)) {
            $variantsData = json_decode($request->variants, true);
            if (is_array($variantsData)) {
                foreach ($variantsData as &$vData) {
                    foreach (['base_price', 'sell_price', 'stock_qty', 'min_order_qty', 'sort_order', 'length', 'width', 'height', 'weight', 'package_length', 'package_width', 'package_height', 'package_weight'] as $field) {
                        if (isset($vData[$field]) && trim((string)$vData[$field]) === '') {
                            $vData[$field] = null;
                        }
                    }
                    if (isset($vData['price']) && (!isset($vData['sell_price']) || $vData['sell_price'] === null)) {
                        $vData['sell_price'] = $vData['price'];
                    }
                    if (!isset($vData['sell_price']) || $vData['sell_price'] === null) {
                        $vData['sell_price'] = isset($vData['base_price']) && $vData['base_price'] !== null ? (float)$vData['base_price'] : 0;
                    }
                    if (!isset($vData['base_price']) || $vData['base_price'] === null) {
                        $vData['base_price'] = isset($vData['sell_price']) && $vData['sell_price'] !== null ? (float)$vData['sell_price'] : 0;
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
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'slug')->ignore($id)->where(fn ($q) => $q->where('deleted', false)),
            ],
            'thumbnail' => 'nullable|string|max:255',
            'alt_text' => 'nullable|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'warranty_duration' => 'nullable|string|max:255',
            'courier_type' => 'nullable|string|in:toko,ekspedisi,expedisi,keduanya',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'best_seller' => 'boolean',
            'is_new' => 'boolean',
            'status' => 'boolean',
            'show_on_web' => 'nullable|boolean',
            'category_id' => 'nullable|string|exists:product_category,id',
            'brand_id' => 'nullable|string|exists:brands,id',
            'tags' => 'nullable|array',
            'tags.*' => 'nullable|string|exists:product_tags,id',
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
            'variants.*.image' => 'nullable|string|max:500',
            'variants.*.base_price' => 'nullable|numeric|min:0',
            'variants.*.sell_price' => 'nullable|numeric|min:0',
            'variants.*.length' => 'nullable|numeric|min:0',
            'variants.*.width' => 'nullable|numeric|min:0',
            'variants.*.height' => 'nullable|numeric|min:0',
            'variants.*.weight' => 'nullable|numeric|min:0',
            'variants.*.package_length' => 'nullable|numeric|min:0',
            'variants.*.package_width' => 'nullable|numeric|min:0',
            'variants.*.package_height' => 'nullable|numeric|min:0',
            'variants.*.package_weight' => 'nullable|numeric|min:0',
            'variants.*.stock_qty' => 'nullable|integer|min:0',
            'variants.*.min_order_qty' => 'nullable|integer|min:0',
        ], [
            'name.required' => 'Nama produk wajib diisi.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',
            'brand_id.exists' => 'Brand yang dipilih tidak valid.',
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

            $validated['courier_type'] = $validated['courier_type'] ?? 'keduanya';
            $validated['show_on_web'] = $request->has('show_on_web') ? (bool)$request->input('show_on_web') : ($product->show_on_web ?? true);

            $targetSlug = !empty($validated['slug']) ? $validated['slug'] : \Illuminate\Support\Str::slug($validated['name'] ?? $product->name);
            $oldSlug = $product->slug;
            if (!empty($oldSlug) && !empty($targetSlug) && $oldSlug !== $targetSlug) {
                $previousSlugs = is_array($product->previous_slugs) ? $product->previous_slugs : (json_decode($product->previous_slugs, true) ?: []);
                if (!in_array($oldSlug, $previousSlugs)) {
                    $previousSlugs[] = $oldSlug;
                }
                $validated['previous_slugs'] = array_values(array_unique($previousSlugs));
            }

            $product->update($validated);

            if (!empty($validated['thumbnail'])) {
                \Illuminate\Support\Facades\Log::channel('media')->info('Product thumbnail updated to S3 path', [
                    'product_id' => $product->id,
                    'thumbnail'  => $validated['thumbnail'],
                    'upload_mode' => $request->hasFile('thumbnail_file') ? 'server_s3_upload' : 'direct_s3_upload',
                ]);
            }

            // Handle Images (Gallery images only, excluding variant images)
            if ($request->has('existing_images')) {
                $existingImages = $request->input('existing_images');
                $existingOrders = $request->input('existing_image_orders');
                foreach ($existingImages as $index => $imageId) {
                    \App\Models\Product\Image::where('id', $imageId)
                        ->where('product_id', $product->id)
                        ->whereNull('variant_id')
                        ->update(['sort_order' => $existingOrders[$index] ?? $index]);
                }
                $removedImages = \App\Models\Product\Image::where('product_id', $product->id)
                    ->whereNull('variant_id')
                    ->whereNotIn('id', $existingImages)
                    ->get();
                foreach ($removedImages as $removedImg) {
                    unlink_media($removedImg->image);
                    $removedImg->delete();
                }
            } else {
                $removedImages = \App\Models\Product\Image::where('product_id', $product->id)
                    ->whereNull('variant_id')
                    ->get();
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
                    unset($colorData['status']);
                    if (!empty($colorData['id'])) {
                        $submittedColorIds[] = $colorData['id'];
                        $color = Color::find($colorData['id']);
                        if ($color) {
                            $color->update($colorData);
                        }
                    } else {
                        unset($colorData['id']);
                        $newColor = Color::create(array_merge(['product_id' => $product->id], $colorData));
                        if ($newColor) {
                            $submittedColorIds[] = $newColor->id;
                        }
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
                foreach ($validated['variants'] as $idx => $variantData) {
                    $vImage = $variantData['image'] ?? null;
                    unset($variantData['image']);

                    // Map stock_qty from frontend to stock_quantity for database
                    if (array_key_exists('stock_qty', $variantData)) {
                        $variantData['stock_quantity'] = $variantData['stock_qty'];
                        unset($variantData['stock_qty']);
                    }

                    // Keep attributes clean for pos-dealer-web (only variant options, no image key)
                    if (isset($variantData['attributes']) && is_array($variantData['attributes'])) {
                        unset($variantData['attributes']['image'], $variantData['attributes']['image_url']);
                    }

                    // Fallback to product dimensions if variant not specified
                    foreach (['length', 'width', 'height', 'weight'] as $dim) {
                        if (!isset($variantData[$dim]) || $variantData[$dim] === '' || $variantData[$dim] === null) {
                            $variantData[$dim] = $validated[$dim] ?? null;
                        }
                    }
                    
                    $vId = !empty($variantData['id']) ? $variantData['id'] : null;
                    unset($variantData['id']);

                    $variant = null;
                    if ($vId) {
                        $variant = \App\Models\Product\Variant::find($vId);
                    }
                    if (!$variant && !empty($variantData['sku'])) {
                        $variant = \App\Models\Product\Variant::where('product_id', $product->id)
                            ->where('sku', $variantData['sku'])
                            ->first();
                    }

                    if ($variant) {
                        $variantData['deleted'] = false;
                        $variantData['status'] = $variantData['status'] ?? true;
                        $variant->update($variantData);
                        $submittedVariantIds[] = $variant->id;
                    } else {
                        $variant = \App\Models\Product\Variant::create(array_merge(['product_id' => $product->id], $variantData));
                        if ($variant) {
                            $submittedVariantIds[] = $variant->id;
                        }
                    }

                    if ($variant) {
                        \App\Services\InventoryService::ensureVariantInventory($product->id, $variant->id);
                        if ($vImage) {
                            $existingImg = \App\Models\Product\Image::where('variant_id', $variant->id)->first();
                            if ($existingImg) {
                                $existingImg->update([
                                    'image' => $vImage,
                                    'sort_order' => $idx,
                                ]);
                            } else {
                                \App\Models\Product\Image::create([
                                    'product_id' => $product->id,
                                    'variant_id' => $variant->id,
                                    'image' => $vImage,
                                    'sort_order' => $idx,
                                    'status' => true,
                                ]);
                            }
                        } else {
                            \App\Models\Product\Image::where('variant_id', $variant->id)->delete();
                        }
                    }
                }

                // Inactivate / soft-delete removed variants to preserve order history
                $deletedVariants = \App\Models\Product\Variant::where('product_id', $product->id)
                    ->where('deleted', false)
                    ->whereNotIn('id', $submittedVariantIds)
                    ->get();
                foreach ($deletedVariants as $delV) {
                    \App\Models\Product\Image::where('variant_id', $delV->id)->delete();
                    $delV->update([
                        'deleted' => true,
                        'status' => false,
                    ]);
                }
            } elseif ($request->has('variants') && is_array($request->variants) && empty($request->variants)) {
                \App\Models\Product\Image::where('product_id', $product->id)->whereNotNull('variant_id')->delete();
                \App\Models\Product\Variant::where('product_id', $product->id)->update([
                    'deleted' => true,
                    'status' => false,
                ]);
            }

            if ($request->has('tags') || $request->ajax() || $request->wantsJson()) {
                $submittedTagIds = is_array($request->tags) ? array_filter($request->tags) : [];
                $existingRelations = TagRelation::where('product_id', $product->id)->get();
                $existingTagIds = $existingRelations->pluck('tag_id')->toArray();

                $toDelete = array_diff($existingTagIds, $submittedTagIds);
                if (!empty($toDelete)) {
                    TagRelation::where('product_id', $product->id)
                        ->whereIn('tag_id', $toDelete)
                        ->delete();
                }

                $toInsert = array_diff($submittedTagIds, $existingTagIds);
                foreach ($toInsert as $tId) {
                    TagRelation::create([
                        'product_id' => $product->id,
                        'tag_id' => $tId,
                    ]);
                }
            }

            \Log::channel('product')->info("Product updated successfully: " . $product->id);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Produk berhasil diperbarui.',
                    'redirect_url' => route('products.show', $product->id),
                ]);
            }

            return redirect()->route('products.show', $product->id)->with('success', 'Produk berhasil diperbarui.');
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

    public function destroy(Request $request, $id)
    {
        $product = Product::withoutGlobalScope('not-deleted')->with(['images', 'variants'])->findOrFail($id);
        
        // Guard: Check if this product or any of its variants has orders
        $variantIds = $product->variants->pluck('id')->filter()->toArray();
        $hasOrders = DB::table('order_items')
            ->where('product_id', $product->id)
            ->when(!empty($variantIds), function ($q) use ($variantIds) {
                $q->orWhereIn('product_variant_id', $variantIds);
            })
            ->exists();

        if ($hasOrders) {
            $msg = 'Produk tidak dapat dihapus karena sudah memiliki riwayat pesanan (order).';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                ], 422);
            }
            return redirect()->route('products.index')->with('error', $msg);
        }

        // Soft delete product & variants
        $product->update([
            'deleted' => true,
            'status' => false,
            'show_on_web' => false,
            'slug' => $product->slug . '-deleted-' . time(),
        ]);

        \App\Models\Product\Variant::where('product_id', $product->id)->update([
            'deleted' => true,
            'status' => false,
        ]);

        \Log::channel('product')->info("Product marked as deleted successfully: " . $id);

        $msg = 'Produk berhasil dihapus.';
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
            ]);
        }

        return redirect()->route('products.index')->with('success', $msg);
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

    public function export(Request $request, \App\Services\ProductExportService $exportService)
    {
        return $exportService->download($request);
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

            $createdVariant = Variant::create(array_merge(
                ['product_id' => $product->id],
                $variantData
            ));
            \App\Services\InventoryService::ensureVariantInventory($product->id, $createdVariant->id);
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
