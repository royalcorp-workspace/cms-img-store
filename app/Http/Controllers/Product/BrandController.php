<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::orderBy('sort_order')->get();
        return view('pages.brands.index', compact('brands'));
    }

    public function create()
    {
        return view('pages.brands.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:brands,name',
            'slug' => 'nullable|string|max:255|unique:brands,slug',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'banner_type' => 'required|in:1,2',
            'embed_web' => 'nullable|string',
            'embed_mobile' => 'nullable|string',
            'banner_link' => 'nullable|string|max:1000',
            'banner_web' => 'nullable|image|max:2048',
            'banner_mobile' => 'nullable|image|max:2048',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        $validated['id'] = (string) Str::uuid();
        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['name']);
        $validated['status'] = $request->has('status');
        $validated['is_featured'] = $request->has('is_featured');

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('brands', 'public');
        }
        if ($request->hasFile('banner_web')) {
            $validated['banner_web'] = $request->file('banner_web')->store('brands', 'public');
        }
        if ($request->hasFile('banner_mobile')) {
            $validated['banner_mobile'] = $request->file('banner_mobile')->store('brands', 'public');
        }

        Brand::create($validated);

        return redirect()->route('brands.index')->with('success', 'Brand created successfully.');
    }

    public function edit($id)
    {
        $brand = Brand::findOrFail($id);
        return view('pages.brands.edit', compact('brand'));
    }

    public function update(Request $request, $id)
    {
        $brand = Brand::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:brands,name,' . $id,
            'slug' => 'nullable|string|max:255|unique:brands,slug,' . $id,
            'description' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'banner_type' => 'required|in:1,2',
            'embed_web' => 'nullable|string',
            'embed_mobile' => 'nullable|string',
            'banner_link' => 'nullable|string|max:1000',
            'banner_web' => 'nullable|image|max:2048',
            'banner_mobile' => 'nullable|image|max:2048',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['name']);
        $validated['status'] = $request->has('status');
        $validated['is_featured'] = $request->has('is_featured');

        if ($request->hasFile('logo')) {
            if ($brand->logo) {
                Storage::disk('public')->delete($brand->logo);
            }
            $validated['logo'] = $request->file('logo')->store('brands', 'public');
        }
        if ($request->hasFile('banner_web')) {
            if ($brand->banner_web) {
                Storage::disk('public')->delete($brand->banner_web);
            }
            $validated['banner_web'] = $request->file('banner_web')->store('brands', 'public');
        }
        if ($request->hasFile('banner_mobile')) {
            if ($brand->banner_mobile) {
                Storage::disk('public')->delete($brand->banner_mobile);
            }
            $validated['banner_mobile'] = $request->file('banner_mobile')->store('brands', 'public');
        }

        $brand->update($validated);

        return redirect()->route('brands.index')->with('success', 'Brand updated successfully.');
    }

    public function destroy($id)
    {
        $brand = Brand::findOrFail($id);
        $brand->update(['deleted' => true]);

        return redirect()->route('brands.index')->with('success', 'Brand deleted successfully.');
    }
}
