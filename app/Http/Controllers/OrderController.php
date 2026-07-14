<?php

namespace App\Http\Controllers;

use App\Models\Order\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::query()->with('customer');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'ilike', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'ilike', "%{$search}%");
                  });
            });
        }

        if ($request->has('status') && $request->query('status') !== '') {
            $query->where('status', $request->query('status'));
        }

        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->query('payment_status'));
        }

        $startDate = $request->query('start_date', now()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());

        $query->whereDate('created_at', '>=', $startDate)
              ->whereDate('created_at', '<=', $endDate);

        $orders = $query->orderByDesc('created_at')->paginate(10)->appends($request->query());

        $stats = [
            'total' => Order::whereDate('created_at', '>=', $startDate)
                           ->whereDate('created_at', '<=', $endDate)
                           ->count(),
            Order::STATUS_DRAFT => Order::where('status', Order::STATUS_DRAFT)
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->count(),
            Order::STATUS_PENDING_APPROVAL => Order::where('status', Order::STATUS_PENDING_APPROVAL)
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->count(),
            Order::STATUS_CONFIRMED => Order::where('status', Order::STATUS_CONFIRMED)
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->count(),
            Order::STATUS_PROCESSING => Order::where('status', Order::STATUS_PROCESSING)
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->count(),
            Order::STATUS_SHIPPED => Order::where('status', Order::STATUS_SHIPPED)
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->count(),
            Order::STATUS_DELIVERED => Order::where('status', Order::STATUS_DELIVERED)
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->count(),
            Order::STATUS_CANCELLED => Order::where('status', Order::STATUS_CANCELLED)
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->count(),
            Order::STATUS_RETURNED => Order::where('status', Order::STATUS_RETURNED)
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->count(),
        ];

        return view('pages.orders.index', compact('orders', 'stats', 'startDate', 'endDate'));
    }

    public function show(string $id)
    {
        $order = Order::with(['customer', 'items.product', 'items.variant'])->findOrFail($id);
        return view('pages.orders.show', compact('order'));
    }

    public function verifyPayment(Request $request, string $id)
    {
        $order = Order::findOrFail($id);

        if ((int)$order->payment_status === Order::PAYMENT_PAID) {
            return redirect()->route('orders.show', $id)->with('error', 'Pembayaran order ini sudah lunas (Paid) dan tidak dapat direkonsiliasi kembali.');
        }

        $validated = $request->validate([
            'payment_status' => 'required|integer|in:0,1,2,3,4',
            'status' => 'required|integer|in:0,1,2,3,4,5,6,7',
            'reconciliation_notes' => 'nullable|string|max:1000',
        ]);

        $meta = $order->meta ?? [];
        $meta['reconciliation_notes'] = $validated['reconciliation_notes'];
        $meta['reconciled_at'] = now()->toDateTimeString();
        $meta['reconciled_by'] = auth()->user()->name ?? 'admin';

        $order->update([
            'payment_status' => $validated['payment_status'],
            'status' => $validated['status'],
            'meta' => $meta,
            'editor' => auth()->user()->name ?? 'admin',
        ]);

        return redirect()->route('orders.show', $id)->with('success', 'Order payment reconciliation completed successfully');
    }
}
