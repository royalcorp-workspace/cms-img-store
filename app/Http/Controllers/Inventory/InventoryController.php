<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Inventory;
use App\Models\Product\Product;
use App\Models\Product\Variant;
use App\Models\Store\Store;
use App\Models\Store\StoreChannel;
use App\Models\Warehouse\Warehouse;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $warehouseId = $request->input('warehouse_id');
        $channelId = $request->input('store_channel_id');
        $status = $request->input('status');
        $search = $request->input('search');

        $productQuery = Product::with([
            'images',
            'category',
            'brand',
            'variants' => function ($vq) use ($warehouseId, $channelId) {
                $vq->where('deleted', false)
                   ->orderBy('created_at', 'asc')
                   ->orderBy('id', 'asc')
                   ->with(['inventories' => function ($iq) use ($warehouseId, $channelId) {
                       $iq->where('deleted', false)->with(['warehouse', 'channel']);
                       if ($warehouseId) {
                           $iq->where('warehouse_id', $warehouseId);
                       }
                       if ($channelId) {
                           $iq->where('store_channel_id', $channelId);
                       }
                   }]);
            }
        ])->where('deleted', false)->where('is_bundle', false);

        // Search by Product name, Code, or Variant name, SKU, Barcode
        if ($search) {
            $productQuery->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('code', 'ilike', "%{$search}%")
                  ->orWhereHas('variants', function ($vq) use ($search) {
                      $vq->where('variant_name', 'ilike', "%{$search}%")
                         ->orWhere('sku', 'ilike', "%{$search}%")
                         ->orWhere('barcode', 'ilike', "%{$search}%");
                  });
            });
        }

        // Filter by warehouse
        if ($warehouseId) {
            $productQuery->whereHas('variants.inventories', function ($iq) use ($warehouseId) {
                $iq->where('warehouse_id', $warehouseId)->where('deleted', false);
            });
        }

        // Filter by store channel
        if ($channelId) {
            $productQuery->whereHas('variants.inventories', function ($iq) use ($channelId) {
                $iq->where('store_channel_id', $channelId)->where('deleted', false);
            });
        }

        // Filter by status
        if ($status) {
            switch ($status) {
                case 'available':
                    $productQuery->whereHas('variants.inventories', function ($iq) {
                        $iq->where('available', '>', 0)->where('deleted', false);
                    });
                    break;
                case 'out_of_stock':
                    $productQuery->whereDoesntHave('variants.inventories', function ($iq) {
                        $iq->where('available', '>', 0)->where('deleted', false);
                    });
                    break;
                case 'on_stock':
                    $productQuery->whereHas('variants.inventories', function ($iq) {
                        $iq->where('on_stock', '>', 0)->where('deleted', false);
                    });
                    break;
                case 'incoming':
                    $productQuery->whereHas('variants.inventories', function ($iq) {
                        $iq->where('incoming', '>', 0)->where('deleted', false);
                    });
                    break;
                case 'on_order':
                    $productQuery->whereHas('variants.inventories', function ($iq) {
                        $iq->where('on_order', '>', 0)->where('deleted', false);
                    });
                    break;
                case 'outgoing':
                    $productQuery->whereHas('variants.inventories', function ($iq) {
                        $iq->where('outgoing', '>', 0)->where('deleted', false);
                    });
                    break;
            }
        }

        // Order by latest updated product
        $products = $productQuery->orderBy('updated_at', 'desc')->paginate(10)->withQueryString();

        // Stats summary
        $stats = [
            'total_on_stock' => Inventory::where('deleted', false)->sum('on_stock'),
            'total_incoming' => Inventory::where('deleted', false)->sum('incoming'),
            'total_on_order' => Inventory::where('deleted', false)->sum('on_order'),
            'total_outgoing' => Inventory::where('deleted', false)->sum('outgoing'),
            'total_available' => Inventory::where('deleted', false)->sum('available'),
            'out_of_stock' => Inventory::where('deleted', false)->where('available', '<=', 0)->count(),
            'total_items' => Inventory::where('deleted', false)->count(),
            'total_products' => Product::where('deleted', false)->where('is_bundle', false)->count(),
            'total_variants' => Variant::where('deleted', false)->count(),
        ];

        $warehouses = Warehouse::where('status', true)->orderBy('name')->get();
        $channels = StoreChannel::with('store')->orderBy('name')->get();
        $defaultWarehouse = InventoryService::getDefaultWarehouse();
        $defaultChannel = InventoryService::getWebImgChannel();

        return view('pages.inventory.index', compact('products', 'stats', 'warehouses', 'channels', 'defaultWarehouse', 'defaultChannel'));
    }

    public function searchChannels(Request $request)
    {
        $q = $request->input('q') ?? $request->input('term') ?? '';

        $query = StoreChannel::with('store')->where('deleted', false)->where('status', true);

        if (!empty($q)) {
            $query->where(function ($queryBuilder) use ($q) {
                $queryBuilder->where('name', 'ilike', "%{$q}%")
                             ->orWhere('code', 'ilike', "%{$q}%")
                             ->orWhereHas('store', function ($sq) use ($q) {
                                 $sq->where('name', 'ilike', "%{$q}%")
                                    ->orWhere('code', 'ilike', "%{$q}%");
                             });
            });
        }

        $channels = $query->orderBy('name')->limit(30)->get();

        $results = $channels->map(function ($ch) {
            $storeName = ($ch->store ? $ch->store->name : null) ?? '-';
            return [
                'id' => $ch->id,
                'text' => "{$ch->name} (Toko: {$storeName})",
                'name' => $ch->name,
                'code' => $ch->code,
                'store_name' => $storeName,
            ];
        });

        return response()->json([
            'results' => $results,
        ]);
    }

    public function importForm()
    {
        $warehouses = Warehouse::where('status', true)->orderBy('name')->get();
        $channels = StoreChannel::with('store')->orderBy('name')->get();
        $defaultWarehouse = InventoryService::getDefaultWarehouse();
        $defaultChannel = InventoryService::getWebImgChannel();

        return view('pages.inventory.import', compact('warehouses', 'channels', 'defaultWarehouse', 'defaultChannel'));
    }

    public function importStore(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:10240',
            'warehouse_id' => 'nullable|uuid|exists:warehouses,id',
            'store_channel_id' => 'nullable|uuid|exists:store_channel,id',
            'mode' => 'required|string|in:set,add',
        ]);

        $defaultWarehouse = InventoryService::getDefaultWarehouse();
        $defaultChannel = InventoryService::getWebImgChannel();

        $formWarehouseId = $request->input('warehouse_id') ?: ($defaultWarehouse?->id);
        $formChannelId = $request->input('store_channel_id') ?: ($defaultChannel?->id);
        $mode = $request->input('mode', 'set');

        $channel = StoreChannel::find($formChannelId) ?: $defaultChannel;
        $fallbackWarehouse = Warehouse::find($formWarehouseId) ?: $defaultWarehouse;

        try {
            $path = $request->file('file')->getRealPath();
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
            $sheet = $spreadsheet->getActiveSheet();
            $rawRows = $sheet->toArray(null, true, true, true);
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membaca file spreadsheet: ' . $e->getMessage());
        }

        if (count($rawRows) < 2) {
            return back()->with('error', 'File spreadsheet kosong atau tidak memiliki baris data.');
        }

        $headerRow = array_shift($rawRows);
        $headers = [];
        foreach ($headerRow as $colKey => $colVal) {
            $headers[$colKey] = strtolower(trim((string)$colVal));
        }

        // Check if 'sku' and 'incoming' exist in header
        if (!in_array('sku', $headers) || !in_array('incoming', $headers)) {
            return back()->with('error', 'Header file harus menyertakan kolom "sku" dan "incoming".');
        }

        $skuCol = array_search('sku', $headers);
        $incomingCol = array_search('incoming', $headers);
        $whCol = array_search('warehouse_code', $headers);

        $success = 0;
        $failed = 0;
        $skipped = 0;
        $errors = [];

        // Preload warehouses indexed by uppercase code for fast lookup
        $warehousesByCode = Warehouse::all()->keyBy(fn($w) => strtoupper(trim($w->code)));

        foreach ($rawRows as $rowIndex => $row) {
            // Ignore empty rows
            if (empty(array_filter($row, fn($v) => !is_null($v) && trim((string)$v) !== ''))) {
                continue;
            }

            $rowNumber = $rowIndex; // row number in spreadsheet
            $sku = trim((string)($row[$skuCol] ?? ''));

            if (empty($sku)) {
                $skipped++;
                continue;
            }

            // Find variant by SKU
            $variant = Variant::where('sku', $sku)->first();
            if (!$variant) {
                $failed++;
                $errors[] = [
                    'row' => $rowNumber,
                    'sku' => $sku,
                    'message' => "SKU '{$sku}' tidak ditemukan di katalog produk."
                ];
                continue;
            }

            // Validate incoming
            $incomingVal = $row[$incomingCol] ?? null;
            if ($incomingVal === null || trim((string)$incomingVal) === '' || !is_numeric($incomingVal) || (int)$incomingVal < 0) {
                $failed++;
                $errors[] = [
                    'row' => $rowNumber,
                    'sku' => $sku,
                    'message' => "Nilai incoming '{$incomingVal}' tidak valid (harus angka positif atau nol)."
                ];
                continue;
            }
            $incomingQty = (int) $incomingVal;

            // Determine target warehouse
            $targetWarehouse = $fallbackWarehouse;
            if ($whCol && !empty(trim((string)($row[$whCol] ?? '')))) {
                $whCode = strtoupper(trim((string)$row[$whCol]));
                if (isset($warehousesByCode[$whCode])) {
                    $targetWarehouse = $warehousesByCode[$whCode];
                }
            }

            if (!$targetWarehouse) {
                $failed++;
                $errors[] = [
                    'row' => $rowNumber,
                    'sku' => $sku,
                    'message' => "Gudang tujuan tidak ditemukan."
                ];
                continue;
            }

            // Find or create inventory row
            $inventory = Inventory::where('product_variant_id', $variant->id)
                ->where('warehouse_id', $targetWarehouse->id)
                ->where('store_channel_id', $channel->id)
                ->where('deleted', false)
                ->first();

            if (!$inventory) {
                $inventory = Inventory::create([
                    'id' => \Illuminate\Support\Str::uuid()->toString(),
                    'product_id' => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'warehouse_id' => $targetWarehouse->id,
                    'store_id' => $channel->store_id,
                    'store_channel_id' => $channel->id,
                    'incoming' => 0,
                    'on_order' => 0,
                    'outgoing' => 0,
                    'available' => 0,
                    'quantity' => 0,
                    'creator' => Auth::user()?->name ?? 'Import SKU',
                    'editor' => Auth::user()?->name ?? 'Import SKU',
                    'deleted' => false,
                ]);
            }

            if ($mode === 'add') {
                $inventory->incoming = (int)$inventory->incoming + $incomingQty;
            } else {
                $inventory->incoming = $incomingQty;
            }

            $inventory->editor = Auth::user()?->name ?? 'Import SKU';
            $inventory->save();

            $success++;
        }

        $result = [
            'success' => $success,
            'failed' => $failed,
            'skipped' => $skipped,
            'errors' => $errors,
        ];

        return back()->with('import_result', $result);
    }

    public function importTemplate()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('inventory_incoming');

        // Headers
        $headers = ['sku', 'incoming', 'warehouse_code', 'reference_product_name'];
        $sheet->fromArray($headers, null, 'A1');

        // Style header row
        $sheet->getStyle('A1:D1')->getFont()->setBold(true);

        // Prepopulate with up to 10 actual SKUs from DB as examples
        $variants = Variant::with('product')
            ->whereNotNull('sku')
            ->where('sku', '!=', '')
            ->where('deleted', false)
            ->limit(10)
            ->get();

        $defaultWarehouse = InventoryService::getDefaultWarehouse();
        $whCode = $defaultWarehouse?->code ?? 'GD-JKT01';

        $rowIdx = 2;
        foreach ($variants as $v) {
            $sheet->fromArray([
                $v->sku,
                50, // sample incoming qty
                $whCode,
                ($v->product?->name ?? '') . ' - ' . ($v->variant_name ?? '')
            ], null, 'A' . $rowIdx);
            $rowIdx++;
        }

        // Auto size columns
        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'template_import_incoming_sku.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function create()
    {
        $products = Product::with(['variants' => function ($q) {
            $q->where('deleted', false)
              ->with(['inventories' => function ($iq) {
                  $iq->where('deleted', false);
              }])
              ->orderBy('created_at', 'asc')
              ->orderBy('id', 'asc');
        }])->where('deleted', false)->orderBy('name')->get();

        $warehouses = Warehouse::where('status', true)->orderBy('name')->get();
        $channels = StoreChannel::with('store')->orderBy('name')->get();
        $stores = Store::where('status', true)->orderBy('name')->get();

        $defaultWarehouse = InventoryService::getDefaultWarehouse();
        $defaultChannel = InventoryService::getWebImgChannel();

        $productsData = $products->map(function ($prod) {
            return [
                'id' => $prod->id,
                'name' => $prod->name,
                'code' => $prod->code ?? '-',
                'variants' => $prod->variants->map(function ($v) {
                    return [
                        'id' => $v->id,
                        'variant_name' => $v->variant_name ?: 'Standar',
                        'sku' => $v->sku ?: '-',
                        'stock_quantity' => (int) $v->stock_quantity,
                        'inventories' => $v->inventories->map(function ($inv) {
                            return [
                                'warehouse_id' => $inv->warehouse_id,
                                'store_channel_id' => $inv->store_channel_id,
                                'on_stock' => (int) $inv->on_stock,
                                'incoming' => (int) $inv->incoming,
                                'on_order' => (int) $inv->on_order,
                                'outgoing' => (int) $inv->outgoing,
                                'available' => (int) $inv->available,
                            ];
                        })->values(),
                    ];
                })->values(),
            ];
        });

        return view('pages.inventory.create', compact('products', 'productsData', 'warehouses', 'channels', 'stores', 'defaultWarehouse', 'defaultChannel'));
    }

    public function store(Request $request)
    {
        $defaultWarehouse = InventoryService::getDefaultWarehouse();
        $defaultChannel = InventoryService::getWebImgChannel();

        $warehouseId = $request->input('warehouse_id') ?: ($defaultWarehouse?->id);
        $channelId = $request->input('store_channel_id') ?: ($defaultChannel?->id);
        $storeId = $request->input('store_id');
        if (!$storeId && $channelId) {
            $channel = StoreChannel::find($channelId);
            $storeId = $channel?->store_id;
        }

        // Multi-variant batch stock update / create
        if ($request->has('variants') && is_array($request->input('variants')) && count($request->input('variants')) > 0) {
            $validated = $request->validate([
                'product_id' => 'required|uuid|exists:products,id',
                'warehouse_id' => 'nullable|uuid|exists:warehouses,id',
                'store_id' => 'nullable|uuid|exists:stores,id',
                'store_channel_id' => 'nullable|uuid|exists:store_channel,id',
                'variants' => 'required|array|min:1',
                'variants.*.product_variant_id' => 'required|uuid|exists:product_variants,id',
                'variants.*.on_stock' => 'required|integer|min:0',
                'variants.*.incoming' => 'nullable|integer|min:0',
                'variants.*.on_order' => 'nullable|integer|min:0',
                'variants.*.outgoing' => 'nullable|integer|min:0',
            ]);

            $savedCount = 0;
            DB::transaction(function () use ($validated, $warehouseId, $channelId, $storeId, &$savedCount) {
                $user = Auth::user()?->name ?? 'Admin';
                foreach ($validated['variants'] as $item) {
                    $onStock = (int) $item['on_stock'];
                    $incoming = (int) ($item['incoming'] ?? 0);
                    $onOrder = (int) ($item['on_order'] ?? 0);
                    $outgoing = (int) ($item['outgoing'] ?? 0);
                    $available = max(0, $onStock - $onOrder - $outgoing);

                    Inventory::updateOrCreate(
                        [
                            'product_id' => $validated['product_id'],
                            'product_variant_id' => $item['product_variant_id'],
                            'warehouse_id' => $warehouseId,
                            'store_channel_id' => $channelId,
                        ],
                        [
                            'store_id' => $storeId,
                            'on_stock' => $onStock,
                            'incoming' => $incoming,
                            'on_order' => $onOrder,
                            'outgoing' => $outgoing,
                            'available' => $available,
                            'quantity' => $available,
                            'creator' => $user,
                            'editor' => $user,
                            'deleted' => false,
                        ]
                    );

                    // Sync variant stock_quantity cache
                    Variant::where('id', $item['product_variant_id'])->update([
                        'stock_quantity' => Inventory::where('product_variant_id', $item['product_variant_id'])
                            ->where('deleted', false)
                            ->sum('available')
                    ]);

                    $savedCount++;
                }
            });

            return redirect()->route('inventory.index')->with('success', "Stok untuk {$savedCount} varian berhasil disimpan.");
        }

        // Single variant fallback
        $validated = $request->validate([
            'product_id' => 'required|uuid|exists:products,id',
            'product_variant_id' => 'required|uuid|exists:product_variants,id',
            'warehouse_id' => 'nullable|uuid|exists:warehouses,id',
            'store_id' => 'nullable|uuid|exists:stores,id',
            'store_channel_id' => 'nullable|uuid|exists:store_channel,id',
            'on_stock' => 'required|integer|min:0',
            'incoming' => 'nullable|integer|min:0',
            'on_order' => 'nullable|integer|min:0',
            'outgoing' => 'nullable|integer|min:0',
        ]);

        $onStock = (int) $validated['on_stock'];
        $incoming = (int) ($validated['incoming'] ?? 0);
        $onOrder = (int) ($validated['on_order'] ?? 0);
        $outgoing = (int) ($validated['outgoing'] ?? 0);
        $available = max(0, $onStock - $onOrder - $outgoing);

        $inventory = Inventory::updateOrCreate(
            [
                'product_id' => $validated['product_id'],
                'product_variant_id' => $validated['product_variant_id'],
                'warehouse_id' => $warehouseId,
                'store_channel_id' => $channelId,
            ],
            [
                'store_id' => $storeId,
                'on_stock' => $onStock,
                'incoming' => $incoming,
                'on_order' => $onOrder,
                'outgoing' => $outgoing,
                'available' => $available,
                'quantity' => $available,
                'creator' => Auth::user()?->name ?? 'Admin',
                'editor' => Auth::user()?->name ?? 'Admin',
                'deleted' => false,
            ]
        );

        // Also sync variant's stock_quantity cache
        Variant::where('id', $validated['product_variant_id'])->update([
            'stock_quantity' => Inventory::where('product_variant_id', $validated['product_variant_id'])
                ->where('deleted', false)
                ->sum('available')
        ]);

        return redirect()->route('inventory.index')->with('success', 'Stok inventory berhasil disimpan.');
    }

    public function edit($id)
    {
        $inventory = Inventory::with(['product', 'variant', 'warehouse', 'store', 'channel'])->findOrFail($id);
        $warehouses = Warehouse::where('status', true)->orderBy('name')->get();
        $channels = StoreChannel::with('store')->orderBy('name')->get();

        return view('pages.inventory.edit', compact('inventory', 'warehouses', 'channels'));
    }

    public function update(Request $request, $id)
    {
        $inventory = Inventory::findOrFail($id);

        $validated = $request->validate([
            'warehouse_id' => 'required|uuid|exists:warehouses,id',
            'store_channel_id' => 'required|uuid|exists:store_channel,id',
            'on_stock' => 'required|integer|min:0',
            'incoming' => 'required|integer|min:0',
            'on_order' => 'required|integer|min:0',
            'outgoing' => 'required|integer|min:0',
        ]);

        $channel = StoreChannel::find($validated['store_channel_id']);

        $onStock = (int) $validated['on_stock'];
        $incoming = (int) $validated['incoming'];
        $onOrder = (int) $validated['on_order'];
        $outgoing = (int) $validated['outgoing'];
        $available = max(0, $onStock - $onOrder - $outgoing);

        $inventory->update([
            'warehouse_id' => $validated['warehouse_id'],
            'store_id' => $channel?->store_id ?? $inventory->store_id,
            'store_channel_id' => $validated['store_channel_id'],
            'on_stock' => $onStock,
            'incoming' => $incoming,
            'on_order' => $onOrder,
            'outgoing' => $outgoing,
            'available' => $available,
            'quantity' => $available,
            'editor' => Auth::user()?->name ?? 'Admin',
        ]);

        // Sync variant cache
        if ($inventory->product_variant_id) {
            Variant::where('id', $inventory->product_variant_id)->update([
                'stock_quantity' => Inventory::where('product_variant_id', $inventory->product_variant_id)
                    ->where('deleted', false)
                    ->sum('available')
            ]);
        }

        return redirect()->route('inventory.index')->with('success', 'Data inventory berhasil diperbarui.');
    }

    public function quickUpdate(Request $request)
    {
        $validated = $request->validate([
            'variant_id' => 'required|uuid|exists:product_variants,id',
            'product_id' => 'nullable|uuid|exists:products,id',
            'on_stock' => 'required|integer|min:0',
            'warehouse_id' => 'nullable|uuid|exists:warehouses,id',
            'store_channel_id' => 'nullable|uuid|exists:store_channel,id',
        ]);

        $variant = Variant::findOrFail($validated['variant_id']);
        $productId = $validated['product_id'] ?? $variant->product_id;
        $warehouseId = $validated['warehouse_id'] ?? null;
        $channelId = $validated['store_channel_id'] ?? null;

        // Ensure inventory record exists
        $inventory = InventoryService::ensureVariantInventory(
            $productId,
            $variant->id,
            $warehouseId,
            $channelId,
            0
        );

        $onStock = (int) $validated['on_stock'];
        $inventory->on_stock = $onStock;
        $inventory->editor = Auth::user()?->name ?? 'Admin';
        $inventory->save(); // saving hook recalculates available and quantity

        // Sync variant's stock_quantity
        $totalAvailable = (int) Inventory::where('product_variant_id', $variant->id)
            ->where('deleted', false)
            ->sum('available');
        $variant->update(['stock_quantity' => $totalAvailable]);

        return response()->json([
            'success' => true,
            'message' => "Stok varian {$variant->variant_name} berhasil diperbarui.",
            'data' => [
                'inventory_id' => $inventory->id,
                'variant_id' => $variant->id,
                'on_stock' => $inventory->on_stock,
                'available' => $inventory->available,
                'on_order' => $inventory->on_order,
                'outgoing' => $inventory->outgoing,
                'incoming' => $inventory->incoming,
                'variant_stock' => $totalAvailable,
            ]
        ]);
    }

    public function adjust(Request $request, $id)
    {
        $inventory = Inventory::with('variant')->findOrFail($id);

        $validated = $request->validate([
            'type' => 'required|string|in:set,add,receive_incoming,ship_outgoing,complete_delivery',
            'field' => 'nullable|string|in:on_stock,incoming,on_order,outgoing',
            'amount' => 'required|integer',
            'notes' => 'nullable|string|max:255',
        ]);

        $amount = (int) $validated['amount'];
        $type = $validated['type'];
        $field = $validated['field'] ?? 'on_stock';

        if ($type === 'set') {
            $inventory->$field = max(0, $amount);
        } elseif ($type === 'add') {
            $inventory->$field = max(0, (int)$inventory->$field + $amount);
        } elseif ($type === 'receive_incoming') {
            // Incoming arrives at warehouse -> moves from incoming to on_stock
            $transferQty = min((int)$inventory->incoming, max(0, $amount));
            $inventory->incoming = max(0, (int)$inventory->incoming - $transferQty);
            $inventory->on_stock = (int)$inventory->on_stock + $transferQty;
        } elseif ($type === 'ship_outgoing') {
            // Order shipped: moves from on_order to outgoing
            $shipQty = min((int)$inventory->on_order, max(0, $amount));
            $inventory->on_order = max(0, (int)$inventory->on_order - $shipQty);
            $inventory->outgoing = (int)$inventory->outgoing + $shipQty;
        } elseif ($type === 'complete_delivery') {
            // Delivered: outgoing complete, items physically leave on_stock
            $compQty = min((int)$inventory->outgoing, max(0, $amount));
            $inventory->outgoing = max(0, (int)$inventory->outgoing - $compQty);
            $inventory->on_stock = max(0, (int)$inventory->on_stock - $compQty);
        }

        $inventory->editor = Auth::user()?->name ?? 'Admin';
        $inventory->save(); // saving hook calculates available and quantity automatically

        // Sync variant cache
        if ($inventory->product_variant_id) {
            Variant::where('id', $inventory->product_variant_id)->update([
                'stock_quantity' => Inventory::where('product_variant_id', $inventory->product_variant_id)
                    ->where('deleted', false)
                    ->sum('available')
            ]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Stok berhasil disesuaikan.',
                'data' => [
                    'available' => $inventory->available,
                    'incoming' => $inventory->incoming,
                    'on_order' => $inventory->on_order,
                    'outgoing' => $inventory->outgoing,
                    'quantity' => $inventory->quantity,
                ]
            ]);
        }

        return redirect()->back()->with('success', 'Stok berhasil disesuaikan.');
    }

    public function destroy($id)
    {
        $inventory = Inventory::findOrFail($id);
        $variantId = $inventory->product_variant_id;

        $inventory->update([
            'deleted' => true,
            'editor' => Auth::user()?->name ?? 'Admin',
        ]);

        if ($variantId) {
            Variant::where('id', $variantId)->update([
                'stock_quantity' => Inventory::where('product_variant_id', $variantId)
                    ->where('deleted', false)
                    ->sum('available')
            ]);
        }

        return redirect()->route('inventory.index')->with('success', 'Data inventory berhasil dihapus.');
    }
}
