<?php

namespace App\Http\Controllers\Content;

use App\Models\Content\AboutUs;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AboutUsController extends Controller
{
    public function index()
    {
        $about = AboutUs::first();
        return view('pages.content.about.index', compact('about'));
    }

    public function create()
    {
        return view('pages.content.about.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'vision' => 'nullable|string',
            'mission' => 'nullable|string',
            'values' => 'nullable|string',
            'established_year' => 'nullable|integer|min:1800|max:' . date('Y'),
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'logo' => 'nullable|string|max:255',
            'cover_image' => 'nullable|string|max:255',
            'social_media' => 'nullable|array',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['creator'] = auth()->id();
        $validated['editor'] = auth()->id();

        AboutUs::create($validated);

        return redirect()->route('content.about.index')->with('success', 'About Us created successfully');
    }

    public function edit(string $id)
    {
        $about = AboutUs::findOrFail($id);
        return view('pages.content.about.edit', compact('about'));
    }

    public function update(Request $request, string $id)
    {
        $about = AboutUs::findOrFail($id);

        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'vision' => 'nullable|string',
            'mission' => 'nullable|string',
            'values' => 'nullable|string',
            'established_year' => 'nullable|integer|min:1800|max:' . date('Y'),
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'logo' => 'nullable|string|max:255',
            'cover_image' => 'nullable|string|max:255',
            'social_media' => 'nullable|array',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['editor'] = auth()->id();

        $about->update($validated);

        return redirect()->route('content.about.index')->with('success', 'About Us updated successfully');
    }

    public function destroy(string $id)
    {
        $about = AboutUs::findOrFail($id);
        $about->delete();

        return redirect()->route('content.about.index')->with('success', 'About Us deleted successfully');
    }
}
