<?php

namespace App\Http\Controllers\Content;

use App\Models\Content\Faq;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        $query = Faq::query();

        if ($search = $request->query('search')) {
            $query->where('question', 'like', "%{$search}%");
        }

        $faqs = $query->orderBy('sort_order')->orderByDesc('created_at')->paginate(15)->appends($request->query());
        return view('pages.content.faq.index', compact('faqs'));
    }

    public function create()
    {
        return view('pages.content.faq.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_published' => 'boolean',
        ]);

        $validated['creator'] = auth()->id();
        $validated['editor'] = auth()->id();
        $validated['is_published'] = $request->boolean('is_published');

        Faq::create($validated);

        return redirect()->route('content.faq.index')->with('success', 'FAQ created successfully');
    }

    public function edit(string $id)
    {
        $faq = Faq::findOrFail($id);
        return view('pages.content.faq.edit', compact('faq'));
    }

    public function update(Request $request, string $id)
    {
        $faq = Faq::findOrFail($id);

        $validated = $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_published' => 'boolean',
        ]);

        $validated['editor'] = auth()->id();
        $validated['is_published'] = $request->boolean('is_published');

        $faq->update($validated);

        return redirect()->route('content.faq.index')->with('success', 'FAQ updated successfully');
    }

    public function destroy(string $id)
    {
        $faq = Faq::findOrFail($id);
        $faq->delete();

        return redirect()->route('content.faq.index')->with('success', 'FAQ deleted successfully');
    }
}
