<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\StockCard;
use App\Models\Warehouse\Warehouse;
use App\Models\Store\StoreChannel;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class StockCardController extends Controller
{
    public function index(Request $request)
    {
        $query = StockCard::with(['product', 'variant', 'warehouse', 'channel']);

        // Search query (SKU, product name, variant name, reference, notes)
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->whereHas('variant', function ($vq) use ($search) {
                    $vq->where('sku', 'ilike', "%{$search}%")
                       ->orWhere('variant_name', 'ilike', "%{$search}%");
                })
                ->orWhereHas('product', function ($pq) use ($search) {
                    $pq->where('name', 'ilike', "%{$search}%")
                       ->orWhere('code', 'ilike', "%{$search}%");
                })
                ->orWhere('reference_number', 'ilike', "%{$search}%")
                ->orWhere('notes', 'ilike', "%{$search}%")
                ->orWhere('creator', 'ilike', "%{$search}%");
            });
        }

        // Warehouse filter
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->input('warehouse_id'));
        }

        // Channel filter
        if ($request->filled('store_channel_id')) {
            $query->where('store_channel_id', $request->input('store_channel_id'));
        }

        // Transaction type filter
        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->input('transaction_type'));
        }

        // Date range filter
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->input('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->input('end_date'));
        }

        // Stats summary for cards
        $stats = [
            'total_mutations' => StockCard::count(),
            'total_qty_in' => StockCard::sum('qty_in'),
            'total_qty_out' => StockCard::sum('qty_out'),
            'today_mutations' => StockCard::whereDate('created_at', today())->count(),
        ];

        $stockCards = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        $warehouses = Warehouse::where('status', true)->orderBy('name')->get();
        $channels = StoreChannel::with('store')->orderBy('name')->get();
        $defaultWarehouse = InventoryService::getDefaultWarehouse();
        $defaultChannel = InventoryService::getWebImgChannel();

        return view('pages.inventory.stock-card.index', compact(
            'stockCards',
            'stats',
            'warehouses',
            'channels',
            'defaultWarehouse',
            'defaultChannel'
        ));
    }
}
