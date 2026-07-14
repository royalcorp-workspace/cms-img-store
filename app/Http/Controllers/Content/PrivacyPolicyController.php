<?php

namespace App\Http\Controllers\Content;

use App\Models\Content\PrivacyPolicy;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PrivacyPolicyController extends Controller
{
    public function index(Request $request)
    {
        $query = PrivacyPolicy::query();

        if ($search = $request->query('search')) {
            $query->where('title', 'ilike', "%{$search}%");
        }

        $items = $query->orderBy('sort_order')->orderByDesc('created_at')->paginate(15)->appends($request->query());
        return view('pages.content.privacy.index', compact('items'));
    }

    public function create()
    {
        return view('pages.content.privacy.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:privacy_policies,slug',
            'content' => 'required|string',
            'version' => 'nullable|string|max:50',
            'effective_date' => 'nullable|date',
            'is_published' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        $validated['creator'] = auth()->user()->name ?? 'admin';
        $validated['editor'] = auth()->user()->name ?? 'admin';
        $validated['is_published'] = $request->boolean('is_published');

        PrivacyPolicy::create($validated);

        return redirect()->route('content.privacy.index')->with('success', 'Privacy Policy created successfully');
    }

    public function edit(string $id)
    {
        $item = PrivacyPolicy::findOrFail($id);
        return view('pages.content.privacy.edit', compact('item'));
    }

    public function update(Request $request, string $id)
    {
        $item = PrivacyPolicy::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:privacy_policies,slug,' . $id,
            'content' => 'required|string',
            'version' => 'nullable|string|max:50',
            'effective_date' => 'nullable|date',
            'is_published' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        $validated['editor'] = auth()->user()->name ?? 'admin';
        $validated['is_published'] = $request->boolean('is_published');

        $item->update($validated);

        return redirect()->route('content.privacy.index')->with('success', 'Privacy Policy updated successfully');
    }

    public function destroy(string $id)
    {
        $item = PrivacyPolicy::findOrFail($id);
        $item->delete();

        return redirect()->route('content.privacy.index')->with('success', 'Privacy Policy deleted successfully');
    }
}
