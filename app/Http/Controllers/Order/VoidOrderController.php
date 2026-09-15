<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\VoidOrder;
use App\Models\Order\Order;
use App\Models\Order\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VoidOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = VoidOrder::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('void_reason', 'like', "%{$search}%");
            });
        }

        $voidOrders = $query->orderBy('voided_at', 'desc')->paginate(15)->withQueryString();
        return view('pages.orders.void.index', compact('voidOrders'));
    }
    
    public function show(string $id)
    {
        $voidOrder = VoidOrder::findOrFail($id);
        
        // Data format is already array due to model casts
        $orderData = $voidOrder->order_data;
        $itemsData = $voidOrder->order_items_data;
        
        return view('pages.orders.void.show', compact('voidOrder', 'orderData', 'itemsData'));
    }

    public function restore(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:void_orders,id',
        ]);

        $voidOrders = VoidOrder::whereIn('id', $request->ids)->get();

        DB::beginTransaction();
        try {
            $restoredCount = 0;
            foreach ($voidOrders as $voidOrder) {
                // Restore order
                $orderData = $voidOrder->order_data;
                
                // Hapus relasi yang ikut terbawa dari toArray()
                unset($orderData['items'], $orderData['customer'], $orderData['courier'], $orderData['voucher'], $orderData['payments']);
                
                // Tangani field JSON agar tidak Array to string conversion saat insert DB
                if (isset($orderData['meta']) && is_array($orderData['meta'])) {
                    $orderData['meta'] = json_encode($orderData['meta']);
                }

                Order::insert([$orderData]); 

                // Restore items
                $itemsData = $voidOrder->order_items_data;
                if (!empty($itemsData)) {
                    foreach ($itemsData as &$item) {
                        unset($item['order'], $item['product']); // Hapus relasi jika ada
                        if (isset($item['meta']) && is_array($item['meta'])) {
                            $item['meta'] = json_encode($item['meta']);
                        }
                    }
                    OrderItem::insert($itemsData);
                }

                // Delete from void_orders
                $voidOrder->delete();
                $restoredCount++;
            }

            DB::commit();
            return redirect()->route('orders.void.index')->with('success', "{$restoredCount} Order berhasil di-restore.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal me-restore order: ' . $e->getMessage());
        }
    }
}
