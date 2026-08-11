<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Models\Content\HomepageSection;
use Illuminate\Http\Request;

class HomepageSectionController extends Controller
{
    public function index()
    {
        $sections = HomepageSection::orderBy('sort_order')->get();
        return view('pages.content.homepage.index', compact('sections'));
    }

    public function create()
    {
        return view('pages.content.homepage.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'section_key' => 'required|string|unique:homepage_sections,section_key',
            'title' => 'required|string|max:255',
            'sort_order' => 'required|integer',
            'is_visible' => 'boolean',
        ]);

        $validated['is_visible'] = $request->has('is_visible');
        HomepageSection::create($validated);

        return redirect()->route('content.homepage.index')->with('success', 'Homepage section created successfully.');
    }

    public function edit($id)
    {
        $section = HomepageSection::findOrFail($id);
        return view('pages.content.homepage.edit', compact('section'));
    }

    public function update(Request $request, $id)
    {
        $section = HomepageSection::findOrFail($id);

        $validated = $request->validate([
            'section_key' => 'required|string|unique:homepage_sections,section_key,' . $id,
            'title' => 'required|string|max:255',
            'sort_order' => 'required|integer',
            'is_visible' => 'boolean',
            'meta' => 'nullable|array',
        ]);

        $validated['is_visible'] = $request->has('is_visible');
        $section->update($validated);

        return redirect()->route('content.homepage.index')->with('success', 'Homepage section updated successfully.');
    }

    public function destroy($id)
    {
        $section = HomepageSection::findOrFail($id);
        $section->delete();
        return redirect()->route('content.homepage.index')->with('success', 'Homepage section deleted successfully.');
    }

    public function updateOrder(Request $request, $id)
    {
        $section = HomepageSection::findOrFail($id);
        $request->validate(['sort_order' => 'required|integer']);
        $section->update(['sort_order' => $request->sort_order]);
        
        return response()->json(['success' => true, 'message' => 'Order updated successfully.']);
    }
}
