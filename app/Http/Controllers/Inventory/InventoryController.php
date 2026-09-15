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
        $query = Inventory::with([
            'product' => function ($q) {
                $q->with('images');
            },
            'variant',
            'warehouse',
            'store',
            'channel'
        ])->where('deleted', false);

        // Search by Product name, Variant name, SKU, or Barcode
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('product', function ($pq) use ($search) {
                    $pq->where('name', 'ilike', "%{$search}%")
                       ->orWhere('code', 'ilike', "%{$search}%");
                })->orWhereHas('variant', function ($vq) use ($search) {
                    $vq->where('variant_name', 'ilike', "%{$search}%")
                       ->orWhere('sku', 'ilike', "%{$search}%")
                       ->orWhere('barcode', 'ilike', "%{$search}%");
                });
            });
        }

        // Filter by warehouse
        if ($warehouseId = $request->input('warehouse_id')) {
            $query->where('warehouse_id', $warehouseId);
        }

        // Filter by store channel
        if ($channelId = $request->input('store_channel_id')) {
            $query->where('store_channel_id', $channelId);
        }

        // Filter by status
        if ($status = $request->input('status')) {
            switch ($status) {
                case 'available':
                    $query->where('available', '>', 0);
                    break;
                case 'out_of_stock':
                    $query->where('available', '<=', 0);
                    break;
                case 'on_stock':
                    $query->where('on_stock', '>', 0);
                    break;
                case 'incoming':
                    $query->where('incoming', '>', 0);
                    break;
                case 'on_order':
                    $query->where('on_order', '>', 0);
                    break;
                case 'outgoing':
                    $query->where('outgoing', '>', 0);
                    break;
            }
        }

        // Order by latest updated
        $inventories = $query->orderBy('updated_at', 'desc')->paginate(15)->withQueryString();

        // Stats summary
        $stats = [
            'total_on_stock' => Inventory::where('deleted', false)->sum('on_stock'),
            'total_incoming' => Inventory::where('deleted', false)->sum('incoming'),
            'total_on_order' => Inventory::where('deleted', false)->sum('on_order'),
            'total_outgoing' => Inventory::where('deleted', false)->sum('outgoing'),
            'total_available' => Inventory::where('deleted', false)->sum('available'),
            'out_of_stock' => Inventory::where('deleted', false)->where('available', '<=', 0)->count(),
            'total_items' => Inventory::where('deleted', false)->count(),
        ];

        $warehouses = Warehouse::where('status', true)->orderBy('name')->get();
        $channels = StoreChannel::with('store')->orderBy('name')->get();

        return view('pages.inventory.index', compact('inventories', 'stats', 'warehouses', 'channels'));
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
            $storeName = $ch->store?->name ?? '-';
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
            $q->where('deleted', false);
        }])->where('deleted', false)->orderBy('name')->get();

        $warehouses = Warehouse::where('status', true)->orderBy('name')->get();
        $channels = StoreChannel::with('store')->orderBy('name')->get();
        $stores = Store::where('status', true)->orderBy('name')->get();

        $defaultWarehouse = InventoryService::getDefaultWarehouse();
        $defaultChannel = InventoryService::getWebImgChannel();

        return view('pages.inventory.create', compact('products', 'warehouses', 'channels', 'stores', 'defaultWarehouse', 'defaultChannel'));
    }

    public function store(Request $request)
    {
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

        $defaultWarehouse = InventoryService::getDefaultWarehouse();
        $defaultChannel = InventoryService::getWebImgChannel();

        $warehouseId = $validated['warehouse_id'] ?? ($defaultWarehouse?->id);
        $channelId = $validated['store_channel_id'] ?? ($defaultChannel?->id);
        $storeId = $validated['store_id'] ?? ($defaultChannel?->store_id);

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
