<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $query = Brand::orderBy('sort_order');
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }
        $brands = $query->get();
        return view('pages.brands.index', compact('brands'));
    }

    public function show($id)
    {
        $brand = Brand::with(['products' => function ($q) {
            $q->with('category')->latest();
        }])->findOrFail($id);

        return view('pages.brands.show', compact('brand'));
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
            'logo' => $request->hasFile('logo')
                ? 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp,avif,ico|max:5120'
                : 'nullable|string|max:1000',
            'banner_type' => 'required|in:1,2',
            'embed_web' => 'nullable|string',
            'embed_mobile' => 'nullable|string',
            'banner_link' => 'nullable|string|max:1000',
            'banner_web' => $request->hasFile('banner_web')
                ? 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp,avif,ico|max:5120'
                : 'nullable|string|max:1000',
            'banner_mobile' => $request->hasFile('banner_mobile')
                ? 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp,avif,ico|max:5120'
                : 'nullable|string|max:1000',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        $validated['id'] = (string) Str::uuid();
        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['name']);
        $validated['status'] = $request->has('status');
        $validated['is_featured'] = $request->has('is_featured');

        $uploadDisk = config('filesystems.disks.s3.bucket') ? 's3' : 'public';

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('brands', $uploadDisk);
        } elseif ($request->filled('logo')) {
            $validated['logo'] = $request->input('logo');
        }
        if ($request->hasFile('banner_web')) {
            $validated['banner_web'] = $request->file('banner_web')->store('brands', $uploadDisk);
        } elseif ($request->filled('banner_web')) {
            $validated['banner_web'] = $request->input('banner_web');
        }
        if ($request->hasFile('banner_mobile')) {
            $validated['banner_mobile'] = $request->file('banner_mobile')->store('brands', $uploadDisk);
        } elseif ($request->filled('banner_mobile')) {
            $validated['banner_mobile'] = $request->input('banner_mobile');
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
            'logo' => $request->hasFile('logo')
                ? 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp,avif,ico|max:5120'
                : 'nullable|string|max:1000',
            'banner_type' => 'required|in:1,2',
            'embed_web' => 'nullable|string',
            'embed_mobile' => 'nullable|string',
            'banner_link' => 'nullable|string|max:1000',
            'banner_web' => $request->hasFile('banner_web')
                ? 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp,avif,ico|max:5120'
                : 'nullable|string|max:1000',
            'banner_mobile' => $request->hasFile('banner_mobile')
                ? 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp,avif,ico|max:5120'
                : 'nullable|string|max:1000',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['name']);
        $validated['status'] = $request->has('status');
        $validated['is_featured'] = $request->has('is_featured');

        $uploadDisk = config('filesystems.disks.s3.bucket') ? 's3' : 'public';

        if ($request->hasFile('logo')) {
            if ($brand->logo) {
                unlink_media($brand->logo);
            }
            $validated['logo'] = $request->file('logo')->store('brands', $uploadDisk);
        } elseif ($request->filled('logo')) {
            $newLogo = $request->input('logo');
            if ($brand->logo && $brand->logo !== $newLogo) {
                unlink_media($brand->logo);
            }
            $validated['logo'] = $newLogo;
        } else {
            unset($validated['logo']);
        }

        if ($request->hasFile('banner_web')) {
            if ($brand->banner_web) {
                unlink_media($brand->banner_web);
            }
            $validated['banner_web'] = $request->file('banner_web')->store('brands', $uploadDisk);
        } elseif ($request->filled('banner_web')) {
            $newBannerWeb = $request->input('banner_web');
            if ($brand->banner_web && $brand->banner_web !== $newBannerWeb) {
                unlink_media($brand->banner_web);
            }
            $validated['banner_web'] = $newBannerWeb;
        } else {
            unset($validated['banner_web']);
        }

        if ($request->hasFile('banner_mobile')) {
            if ($brand->banner_mobile) {
                unlink_media($brand->banner_mobile);
            }
            $validated['banner_mobile'] = $request->file('banner_mobile')->store('brands', $uploadDisk);
        } elseif ($request->filled('banner_mobile')) {
            $newBannerMobile = $request->input('banner_mobile');
            if ($brand->banner_mobile && $brand->banner_mobile !== $newBannerMobile) {
                unlink_media($brand->banner_mobile);
            }
            $validated['banner_mobile'] = $newBannerMobile;
        } else {
            unset($validated['banner_mobile']);
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
