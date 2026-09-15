<?php

namespace App\Http\Controllers\Shipping;

use App\Http\Controllers\Controller;

use App\Models\Shipping\Courier;
use Illuminate\Http\Request;

class CourierController extends Controller
{
    public function index(Request $request)
    {
        $query = Courier::query()->withoutGlobalScope('active');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'ilike', "%{$search}%")
                  ->orWhere('name', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->query('type'));
        }

        if ($request->filled('courier_type')) {
            $query->where('courier_type', $request->query('courier_type'));
        }

        if ($request->filled('status')) {
            $status = $request->query('status');
            if ($status === 'active') {
                $query->where('is_active', true)->where('deleted', false);
            } elseif ($status === 'inactive') {
                $query->where(function ($q) {
                    $q->where('is_active', false)->orWhere('deleted', true);
                });
            }
        }

        $couriers = $query->orderBy('sort_order')->orderBy('name')->paginate(15)->appends($request->query());

        return view('pages.shipping.courier.index', compact('couriers'));
    }

    public function create()
    {
        $categories = \App\Models\Product\Category::all();
        return view('pages.shipping.courier.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:couriers,code',
            'name' => 'required|string|max:150',
            'courier_type' => 'required|in:toko,expedisi',
            'type' => 'required|integer|in:1,2,3,4',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'category_ids' => 'nullable|array',
        ]);

        $validated['creator'] = auth()->user()->name ?? 'admin';
        $validated['editor'] = auth()->user()->name ?? 'admin';
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['courier_type'] = $validated['courier_type'] ?? 'expedisi';

        $courier = Courier::create($validated);
        
        if (!empty($validated['category_ids'])) {
            $courier->restrictedCategories()->sync($validated['category_ids']);
        }

        return redirect()->route('couriers.index')->with('success', 'Courier created successfully');
    }

    public function edit(string $id)
    {
        $courier = Courier::withoutGlobalScope('active')->findOrFail($id);
        $categories = \App\Models\Product\Category::all();

        return view('pages.shipping.courier.edit', compact('courier', 'categories'));
    }

    public function update(Request $request, string $id)
    {
        $courier = Courier::withoutGlobalScope('active')->findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:couriers,code,' . $id,
            'name' => 'required|string|max:150',
            'courier_type' => 'required|in:toko,expedisi',
            'type' => 'required|integer|in:1,2,3,4',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'category_ids' => 'nullable|array',
        ]);

        $validated['editor'] = auth()->user()->name ?? 'admin';
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['courier_type'] = $validated['courier_type'] ?? 'expedisi';

        $courier->update($validated);
        
        $courier->restrictedCategories()->sync($validated['category_ids'] ?? []);

        return redirect()->route('couriers.index')->with('success', 'Courier updated successfully');
    }

    public function destroy(string $id)
    {
        $courier = Courier::withoutGlobalScope('active')->findOrFail($id);
        $courier->update(['deleted' => true, 'is_active' => false]);

        return redirect()->route('couriers.index')->with('success', 'Courier deleted successfully');
    }
}
