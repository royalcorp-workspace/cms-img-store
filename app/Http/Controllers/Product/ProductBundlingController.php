<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product\Product;
use App\Models\Product\ProductBundling;
use App\Models\Product\ProductBundlingItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductBundlingController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductBundling::with('items.product');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $bundlings = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        return view('pages.bundling.index', compact('bundlings'));
    }

    public function create()
    {
        $products = Product::where('status', true)
            ->where('deleted', false)
            ->with('variants')
            ->orderBy('name')
            ->get();
        return view('pages.bundling.create', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products_bundling,slug',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image' => $request->hasFile('image')
                ? 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp,avif,ico|max:5120'
                : 'nullable|string|max:1000',
            'banner_image' => $request->hasFile('banner_image')
                ? 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp,avif,ico|max:5120'
                : 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|string|exists:products,id',
            'items.*.variant_id' => 'nullable|string|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $validated['id'] = (string) Str::uuid();
        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['name']);
        $validated['is_active'] = $request->has('is_active');

        $uploadDisk = config('filesystems.disks.s3.bucket') ? 's3' : 'public';
        if ($request->hasFile('image')) {
            $validated['image_url'] = $request->file('image')->store('bundlings', $uploadDisk);
        } elseif ($request->filled('image')) {
            $validated['image_url'] = $request->input('image');
        }
        if ($request->hasFile('banner_image')) {
            $validated['banner_image'] = $request->file('banner_image')->store('bundlings', $uploadDisk);
        } elseif ($request->filled('banner_image')) {
            $validated['banner_image'] = $request->input('banner_image');
        }

        $bundling = ProductBundling::create($validated);

        foreach ($request->input('items') as $item) {
            ProductBundlingItem::create([
                'id' => (string) Str::uuid(),
                'product_bundling_id' => $bundling->id,
                'product_id' => $item['product_id'],
                'variant_id' => $item['variant_id'] ?? null,
                'quantity' => $item['quantity'],
            ]);
        }

        return redirect()->route('bundlings.index')->with('success', 'Product bundle created successfully.');
    }

    public function edit($id)
    {
        $bundling = ProductBundling::with('items')->findOrFail($id);
        $products = Product::where('status', true)
            ->where('deleted', false)
            ->with('variants')
            ->orderBy('name')
            ->get();
        return view('pages.bundling.edit', compact('bundling', 'products'));
    }

    public function update(Request $request, $id)
    {
        $bundling = ProductBundling::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products_bundling,slug,' . $id,
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'image' => $request->hasFile('image')
                ? 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp,avif,ico|max:5120'
                : 'nullable|string|max:1000',
            'banner_image' => $request->hasFile('banner_image')
                ? 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp,avif,ico|max:5120'
                : 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|string|exists:products,id',
            'items.*.variant_id' => 'nullable|string|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['name']);
        $validated['is_active'] = $request->has('is_active');

        $uploadDisk = config('filesystems.disks.s3.bucket') ? 's3' : 'public';

        if ($request->hasFile('image')) {
            if ($bundling->image_url) {
                unlink_media($bundling->image_url);
            }
            $validated['image_url'] = $request->file('image')->store('bundlings', $uploadDisk);
        } elseif ($request->filled('image')) {
            $newImage = $request->input('image');
            if ($bundling->image_url && $bundling->image_url !== $newImage) {
                unlink_media($bundling->image_url);
            }
            $validated['image_url'] = $newImage;
        }

        if ($request->hasFile('banner_image')) {
            if ($bundling->banner_image) {
                unlink_media($bundling->banner_image);
            }
            $validated['banner_image'] = $request->file('banner_image')->store('bundlings', $uploadDisk);
        } elseif ($request->filled('banner_image')) {
            $newBanner = $request->input('banner_image');
            if ($bundling->banner_image && $bundling->banner_image !== $newBanner) {
                unlink_media($bundling->banner_image);
            }
            $validated['banner_image'] = $newBanner;
        }

        $bundling->update($validated);

        // Recreate items
        $bundling->items()->delete();
        foreach ($request->input('items') as $item) {
            ProductBundlingItem::create([
                'id' => (string) Str::uuid(),
                'product_bundling_id' => $bundling->id,
                'product_id' => $item['product_id'],
                'variant_id' => $item['variant_id'] ?? null,
                'quantity' => $item['quantity'],
            ]);
        }

        return redirect()->route('bundlings.index')->with('success', 'Product bundle updated successfully.');
    }

    public function destroy($id)
    {
        $bundling = ProductBundling::findOrFail($id);
        $bundling->update(['deleted' => true]);

        if ($bundling->image_url) {
            unlink_media($bundling->image_url);
        }
        if ($bundling->banner_image) {
            unlink_media($bundling->banner_image);
        }

        return redirect()->route('bundlings.index')->with('success', 'Product bundle deleted successfully.');
    }
}
