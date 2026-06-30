<?php

namespace App\Http\Controllers;

use App\Models\Order\Order;
use App\Models\Customer\Customer;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        return view('welcome');
    }

    public function dashboard(Request $request)
    {
        $month = $request->query('month', now()->format('Y-m'));
        [$year, $monthNum] = explode('-', $month);
        $startDate = now()->setDate($year, $monthNum, 1)->startOfDay();
        $endDate = $startDate->copy()->endOfMonth()->endOfDay();

        $stats = [
            'total' => Order::whereBetween('created_at', [$startDate, $endDate])->count(),
            'draft' => Order::where('status', Order::STATUS_DRAFT)->whereBetween('created_at', [$startDate, $endDate])->count(),
            'pending' => Order::where('status', Order::STATUS_PENDING_APPROVAL)->whereBetween('created_at', [$startDate, $endDate])->count(),
            'confirmed' => Order::where('status', Order::STATUS_CONFIRMED)->whereBetween('created_at', [$startDate, $endDate])->count(),
            'processing' => Order::where('status', Order::STATUS_PROCESSING)->whereBetween('created_at', [$startDate, $endDate])->count(),
            'shipped' => Order::where('status', Order::STATUS_SHIPPED)->whereBetween('created_at', [$startDate, $endDate])->count(),
            'delivered' => Order::where('status', Order::STATUS_DELIVERED)->whereBetween('created_at', [$startDate, $endDate])->count(),
            'cancelled' => Order::where('status', Order::STATUS_CANCELLED)->whereBetween('created_at', [$startDate, $endDate])->count(),
            'returned' => Order::where('status', Order::STATUS_RETURNED)->whereBetween('created_at', [$startDate, $endDate])->count(),
        ];

        $orders = Order::with('customer')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('dashboard', compact('orders', 'stats', 'month'));
    }
}
