<?php

namespace App\Http\Controllers\Content;

use App\Models\Content\TermsAndCondition;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TermsAndConditionController extends Controller
{
    public function index(Request $request)
    {
        $query = TermsAndCondition::query();

        if ($search = $request->query('search')) {
            $query->where('title', 'ilike', "%{$search}%");
        }

        $items = $query->orderBy('sort_order')->orderByDesc('created_at')->paginate(15)->appends($request->query());
        return view('pages.content.terms.index', compact('items'));
    }

    public function create()
    {
        return view('pages.content.terms.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:terms_and_conditions,slug',
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

        TermsAndCondition::create($validated);

        return redirect()->route('content.terms.index')->with('success', 'Terms & Conditions created successfully');
    }

    public function edit(string $id)
    {
        $item = TermsAndCondition::findOrFail($id);
        return view('pages.content.terms.edit', compact('item'));
    }

    public function update(Request $request, string $id)
    {
        $item = TermsAndCondition::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:terms_and_conditions,slug,' . $id,
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

        return redirect()->route('content.terms.index')->with('success', 'Terms & Conditions updated successfully');
    }

    public function destroy(string $id)
    {
        $item = TermsAndCondition::findOrFail($id);
        $item->delete();

        return redirect()->route('content.terms.index')->with('success', 'Terms & Conditions deleted successfully');
    }
}
