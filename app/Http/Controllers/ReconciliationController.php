<?php

namespace App\Http\Controllers;

use App\Models\Order\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReconciliationController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->query('date', Carbon::today()->toDateString());
        $carbonDate = Carbon::parse($date);

        $totalOrdersCount = Order::whereDate('created_at', $date)->count();

        $pendingManualOrders = Order::where('status', Order::STATUS_PENDING_APPROVAL)
            ->where('payment_status', '!=', Order::PAYMENT_PAID)
            ->where(function($q) {
                $q->where('payment_method', 'ilike', '%transfer_manual%')
                  ->orWhere('payment_method', 'ilike', '%manual%');
            });
        
        $totalPendingCount = $pendingManualOrders->count();

        $reconciledOrdersQuery = Order::where('payment_status', Order::PAYMENT_PAID)
            ->where(function($q) {
                $q->where('payment_method', 'ilike', '%transfer_manual%')
                  ->orWhere('payment_method', 'ilike', '%manual%');
            })
            ->whereRaw("meta->>'reconciled_at' LIKE ?", ["$date%"]);

        $reconciledCount = (clone $reconciledOrdersQuery)->count();
        $reconciledAmount = (clone $reconciledOrdersQuery)->sum('total');

        $pendingOrdersList = $pendingManualOrders->with('customer')->orderBy('created_at')->get();

        $reconciledOrdersList = $reconciledOrdersQuery->with('customer')->orderByDesc('updated_at')->get();

        return view('pages.reconciliation.index', compact(
            'date',
            'totalOrdersCount',
            'totalPendingCount',
            'reconciledCount',
            'reconciledAmount',
            'pendingOrdersList',
            'reconciledOrdersList'
        ));
    }
}
