<?php

namespace App\Http\Controllers\Content;

use App\Models\Content\HowToReturn;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HowToReturnController extends Controller
{
    public function index(Request $request)
    {
        $query = HowToReturn::query();

        if ($search = $request->query('search')) {
            $query->where('title', 'ilike', "%{$search}%");
        }

        $items = $query->orderBy('sort_order')->orderByDesc('created_at')->paginate(15)->appends($request->query());
        return view('pages.content.how-to-return.index', compact('items'));
    }

    public function create()
    {
        return view('pages.content.how-to-return.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:how_to_returns,slug',
            'content' => 'required|string',
            'steps' => 'nullable|string',
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

        HowToReturn::create($validated);

        return redirect()->route('content.how-to-return.index')->with('success', 'How To Return created successfully');
    }

    public function edit(string $id)
    {
        $item = HowToReturn::findOrFail($id);
        return view('pages.content.how-to-return.edit', compact('item'));
    }

    public function update(Request $request, string $id)
    {
        $item = HowToReturn::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:how_to_returns,slug,' . $id,
            'content' => 'required|string',
            'steps' => 'nullable|string',
            'featured_image' => 'nullable|string|max:255',
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

        $item->update($validated);

        return redirect()->route('content.how-to-return.index')->with('success', 'How To Return updated successfully');
    }

    public function destroy(string $id)
    {
        $item = HowToReturn::findOrFail($id);
        $item->delete();

        return redirect()->route('content.how-to-return.index')->with('success', 'How To Return deleted successfully');
    }
}
