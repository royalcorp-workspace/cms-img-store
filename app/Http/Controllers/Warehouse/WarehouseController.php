<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Warehouse\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $query = Warehouse::withCount([
            'inventories' => function ($q) {
                $q->where('deleted', false);
            }
        ]);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('code', 'ilike', "%{$search}%")
                  ->orWhere('city', 'ilike', "%{$search}%")
                  ->orWhere('address', 'ilike', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->input('status') !== '') {
            $query->where('status', (bool) $request->input('status'));
        }

        $warehouses = $query->orderBy('name')->paginate(15)->withQueryString();

        $stats = [
            'total' => Warehouse::count(),
            'active' => Warehouse::where('status', true)->count(),
            'inactive' => Warehouse::where('status', false)->count(),
        ];

        return view('pages.warehouse.index', compact('warehouses', 'stats'));
    }

    public function create()
    {
        return view('pages.warehouse.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:warehouses,code',
            'name' => 'required|string|max:255',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'status' => 'nullable|boolean',
        ]);

        $validated['status'] = $request->has('status') ? (bool)$request->input('status') : true;

        Warehouse::create($validated);

        return redirect()->route('warehouses.index')->with('success', 'Gudang berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $warehouse = Warehouse::findOrFail($id);
        return view('pages.warehouse.edit', compact('warehouse'));
    }

    public function update(Request $request, $id)
    {
        $warehouse = Warehouse::findOrFail($id);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('warehouses', 'code')->ignore($warehouse->id)],
            'name' => 'required|string|max:255',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'status' => 'nullable|boolean',
        ]);

        $validated['status'] = $request->has('status') ? (bool)$request->input('status') : false;

        $warehouse->update($validated);

        return redirect()->route('warehouses.index')->with('success', 'Gudang berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $warehouse = Warehouse::withCount([
            'inventories' => function ($q) {
                $q->where('deleted', false)->where('available', '>', 0);
            }
        ])->findOrFail($id);

        if ($warehouse->inventories_count > 0) {
            return redirect()->route('warehouses.index')->with('error', 'Gudang tidak dapat dihapus karena masih memiliki inventory dengan stok aktif.');
        }

        $warehouse->delete();

        return redirect()->route('warehouses.index')->with('success', 'Gudang berhasil dihapus.');
    }
}
