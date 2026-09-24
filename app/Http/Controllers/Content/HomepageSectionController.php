<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Models\Content\HomepageSection;
use App\Models\Product\Brand;
use App\Models\Product\Category;
use App\Models\Product\Product;
use App\Models\Product\ProductBundling;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HomepageSectionController extends Controller
{
    public function index()
    {
        $sections = HomepageSection::orderBy('sort_order')->get();
        return view('pages.content.homepage.index', compact('sections'));
    }

    public function create()
    {
        $brands = Brand::where('deleted', false)->orderBy('name')->get();
        $categories = Category::where('deleted', false)->orderBy('name')->get();
        $products = Product::where('deleted', false)->where('status', true)->orderBy('name')->get(['id', 'name', 'code', 'thumbnail']);
        $bundles = ProductBundling::where('deleted', false)->where('is_active', true)->orderBy('name')->get(['id', 'name', 'price', 'image_url']);
        return view('pages.content.homepage.create', compact('brands', 'categories', 'products', 'bundles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'sort_order' => 'required|integer',
            'platform' => 'required|in:web,mobile,all',
            'content_type' => 'required|in:product,category,brand,combination',
        ]);

        $title = $request->input('title');
        $sectionKey = $request->input('section_key');
        if (empty($sectionKey)) {
            $sectionKey = Str::slug($title, '_');
        } else {
            $sectionKey = Str::slug($sectionKey, '_');
        }

        $originalKey = $sectionKey;
        $counter = 1;
        while (HomepageSection::where('section_key', $sectionKey)->exists()) {
            $sectionKey = "{$originalKey}_{$counter}";
            $counter++;
        }

        $meta = [
            'platform' => $request->input('platform', 'all'),
            'content_type' => $request->input('content_type', 'product'),
            'selected_products' => (array) $request->input('selected_products', []),
            'selected_bundles' => (array) $request->input('selected_bundles', []),
            'selected_brands' => (array) $request->input('selected_brands', []),
            'selected_categories' => (array) $request->input('selected_categories', []),
            'custom_note' => $request->input('custom_note'),
        ];

        HomepageSection::create([
            'title' => $title,
            'section_key' => $sectionKey,
            'sort_order' => (int) $request->input('sort_order', 0),
            'is_visible' => $request->has('is_visible'),
            'meta' => $meta,
        ]);

        return redirect()->route('content.homepage.index')->with('success', 'Homepage section created successfully.');
    }

    public function edit($id)
    {
        $section = HomepageSection::findOrFail($id);
        $brands = Brand::where('deleted', false)->orderBy('name')->get();
        $categories = Category::where('deleted', false)->orderBy('name')->get();
        $products = Product::where('deleted', false)->where('status', true)->orderBy('name')->get(['id', 'name', 'code', 'thumbnail']);
        $bundles = ProductBundling::where('deleted', false)->where('is_active', true)->orderBy('name')->get(['id', 'name', 'price', 'image_url']);
        return view('pages.content.homepage.edit', compact('section', 'brands', 'categories', 'products', 'bundles'));
    }

    public function update(Request $request, $id)
    {
        $section = HomepageSection::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'sort_order' => 'required|integer',
            'platform' => 'required|in:web,mobile,all',
            'content_type' => 'required|in:product,category,brand,combination',
        ]);

        $meta = is_array($section->meta) ? $section->meta : [];
        $meta['platform'] = $request->input('platform', 'all');
        $meta['content_type'] = $request->input('content_type', 'product');
        $meta['selected_products'] = (array) $request->input('selected_products', []);
        $meta['selected_bundles'] = (array) $request->input('selected_bundles', []);
        $meta['selected_brands'] = (array) $request->input('selected_brands', []);
        $meta['selected_categories'] = (array) $request->input('selected_categories', []);
        if ($request->has('custom_note')) {
            $meta['custom_note'] = $request->input('custom_note');
        }

        $section->update([
            'title' => $request->input('title'),
            'sort_order' => (int) $request->input('sort_order', 0),
            'is_visible' => $request->has('is_visible'),
            'meta' => $meta,
        ]);

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
