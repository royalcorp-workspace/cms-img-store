<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer\Customer;
use App\Models\Customer\CustomerGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CustomerGroupController extends Controller
{
    public function index(Request $request)
    {
        $query = CustomerGroup::withoutGlobalScope('active');

        if ($search = $request->query('search')) {
            $query->where('name', 'ilike', "%{$search}%");
        }

        $groups = $query->withCount('members')->orderBy('name')->paginate(15)->appends($request->query());

        return view('pages.customer-groups.index', compact('groups'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        return view('pages.customer-groups.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'boolean',
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'exists:customers,id',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');
        $memberIds = $request->input('member_ids', []);
        unset($validated['member_ids']);

        $group = CustomerGroup::create($validated);

        if (!empty($memberIds)) {
            $syncData = [];
            foreach ($memberIds as $id) {
                $syncData[$id] = ['id' => (string) Str::uuid()];
            }
            $group->members()->attach($syncData);
        }

        return redirect()->route('customer-groups.index')->with('success', 'Customer Group berhasil dibuat.');
    }

    public function edit($id)
    {
        $group = CustomerGroup::withoutGlobalScope('active')->with('members')->findOrFail($id);
        $customers = Customer::orderBy('name')->get();
        return view('pages.customer-groups.edit', compact('group', 'customers'));
    }

    public function update(Request $request, $id)
    {
        $group = CustomerGroup::withoutGlobalScope('active')->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'boolean',
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'exists:customers,id',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');
        $memberIds = $request->input('member_ids', []);
        unset($validated['member_ids']);

        $group->update($validated);

        $syncData = [];
        foreach ($memberIds as $mId) {
            $syncData[$mId] = ['id' => (string) Str::uuid()];
        }
        $group->members()->sync($syncData);

        return redirect()->route('customer-groups.index')->with('success', 'Customer Group berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $group = CustomerGroup::withoutGlobalScope('active')->findOrFail($id);
        $group->update(['deleted' => true]);
        return redirect()->route('customer-groups.index')->with('success', 'Customer Group berhasil dihapus.');
    }
}
