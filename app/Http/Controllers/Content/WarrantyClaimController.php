<?php

namespace App\Http\Controllers\Content;

use App\Models\Content\WarrantyClaim;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WarrantyClaimController extends Controller
{
    public function index(Request $request)
    {
        $query = WarrantyClaim::query();

        if ($search = $request->query('search')) {
            $query->where('title', 'ilike', "%{$search}%");
        }

        $items = $query->orderBy('sort_order')->orderByDesc('created_at')->paginate(15)->appends($request->query());
        return view('pages.content.warranty.index', compact('items'));
    }

    public function create()
    {
        return view('pages.content.warranty.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:warranty_claims,slug',
            'content' => 'required|string',
            'steps' => 'nullable|string',
            'required_documents' => 'nullable|string',
            'processing_time_days' => 'nullable|integer|min:1',
            'featured_image' => 'nullable|string|max:255',
            'is_published' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        $validated['creator'] = auth()->user()->name ?? 'admin';
        $validated['editor'] = auth()->user()->name ?? 'admin';
        $validated['is_published'] = $request->boolean('is_published');

        $stepsData = $validated['steps'] ?? null;
        if (is_string($stepsData)) {
            $stepsData = json_decode($stepsData, true) ?: null;
        }
        $validated['steps'] = $stepsData;

        $docsData = $validated['required_documents'] ?? null;
        if (is_string($docsData)) {
            $docsData = json_decode($docsData, true) ?: null;
        }
        $validated['required_documents'] = $docsData;

        WarrantyClaim::create($validated);

        return redirect()->route('content.warranty.index')->with('success', 'Warranty Claim created successfully');
    }

    public function edit(string $id)
    {
        $item = WarrantyClaim::findOrFail($id);
        return view('pages.content.warranty.edit', compact('item'));
    }

    public function update(Request $request, string $id)
    {
        $item = WarrantyClaim::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:warranty_claims,slug,' . $id,
            'content' => 'required|string',
            'steps' => 'nullable|string',
            'required_documents' => 'nullable|string',
            'processing_time_days' => 'nullable|integer|min:1',
            'featured_image' => 'nullable|string|max:255',
            'is_published' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        $validated['editor'] = auth()->user()->name ?? 'admin';
        $validated['is_published'] = $request->boolean('is_published');

        $stepsData = $validated['steps'] ?? null;
        if (is_string($stepsData)) {
            $stepsData = json_decode($stepsData, true) ?: null;
        }
        $validated['steps'] = $stepsData;

        $docsData = $validated['required_documents'] ?? null;
        if (is_string($docsData)) {
            $docsData = json_decode($docsData, true) ?: null;
        }
        $validated['required_documents'] = $docsData;

        $item->update($validated);

        return redirect()->route('content.warranty.index')->with('success', 'Warranty Claim updated successfully');
    }

    public function destroy(string $id)
    {
        $item = WarrantyClaim::findOrFail($id);
        $item->delete();

        return redirect()->route('content.warranty.index')->with('success', 'Warranty Claim deleted successfully');
    }
}
