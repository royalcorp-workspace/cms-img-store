<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;

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

        $manualMethodIds = \App\Models\PaymentMethod::withoutGlobalScope('active')
            ->where(function ($mq) {
                $mq->where('code', 'ilike', '%manual%')
                   ->orWhere('name', 'ilike', '%manual%')
                   ->orWhere('type', 1);
            })->pluck('id')->filter()->values()->toArray();

        $manualFilter = function($q) use ($manualMethodIds) {
            $q->where('payment_method', 'ilike', '%transfer_manual%')
              ->orWhere('payment_method', 'ilike', '%manual%')
              ->orWhereRaw("meta->>'payment_method_code' ILIKE '%manual%'")
              ->orWhereRaw("meta->>'payment_method_name' ILIKE '%manual%'");
            if (!empty($manualMethodIds)) {
                $q->orWhereIn('payment_method', $manualMethodIds);
            }
        };

        $pendingManualOrders = Order::where('status', Order::STATUS_PENDING_APPROVAL)
            ->where('payment_status', '!=', Order::PAYMENT_PAID)
            ->where($manualFilter);
        
        $totalPendingCount = $pendingManualOrders->count();

        $reconciledOrdersQuery = Order::where('payment_status', Order::PAYMENT_PAID)
            ->where($manualFilter)
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
