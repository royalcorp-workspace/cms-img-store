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

        $daysInMonth = (int) now()->setDate($year, $monthNum, 1)->endOfMonth()->format('d');
        $dailyStats = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dayStart = now()->setDate($year, $monthNum, $d)->startOfDay();
            $dayEnd = $dayStart->copy()->endOfDay();
            $dailyStats[] = [
                'day' => $d,
                'total' => Order::whereBetween('created_at', [$dayStart, $dayEnd])->count(),
            ];
        }

        $statusLabels = [
            'draft' => 'Draft',
            'pending' => 'Pending Approval',
            'confirmed' => 'Confirmed',
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
            'returned' => 'Returned',
        ];

        $statusColors = [
            'draft' => '#9ca3af',
            'pending' => '#f59e0b',
            'confirmed' => '#3b82f6',
            'processing' => '#6366f1',
            'shipped' => '#a855f7',
            'delivered' => '#22c55e',
            'cancelled' => '#ef4444',
            'returned' => '#f97316',
        ];

        return view('dashboard', compact('orders', 'stats', 'month', 'dailyStats', 'statusLabels', 'statusColors'));
    }
}
