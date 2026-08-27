<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Order\Settlement;
use Illuminate\Http\Request;

class SettlementController extends Controller
{
    public function index(Request $request)
    {
        $query = Settlement::query();

        if ($search = $request->query('search')) {
            $query->where('reference_id', 'ilike', "%{$search}%");
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $settlements = $query->orderBy('settlement_date', 'desc')->paginate(15)->appends($request->query());

        return view('pages.settlements.index', compact('settlements'));
    }

    public function show(string $id)
    {
        $settlement = Settlement::with('orders')->findOrFail($id);
        return view('pages.settlements.show', compact('settlement'));
    }
}
