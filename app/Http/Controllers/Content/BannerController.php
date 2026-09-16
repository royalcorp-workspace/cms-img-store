<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Models\Content\Banner;
use App\Models\Content\BannerImage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::withCount('images')->orderBy('sort_order')->orderBy('created_at', 'desc')->get();
        return view('pages.banners.index', compact('banners'));
    }

    public function create()
    {
        $brands = \App\Models\Product\Brand::where('deleted', false)->orderBy('name')->get();
        $categories = \App\Models\Product\Category::where('deleted', false)->orderBy('name')->get();
        return view('pages.banners.create', compact('brands', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'link_url'      => 'nullable|string|max:500',
            'type'          => 'required|integer|in:1,3,4',
            'target_id'     => 'nullable|string',
            'device_flag'   => 'required|integer|in:1,2,3',
            'placement_size'=> 'required|integer|in:1,2,3',
            'sort_order'    => 'nullable|integer|min:0',
            'is_active'     => 'boolean',
            'images_web.*'          => 'nullable',
            'images_web_url_text.*' => 'nullable|string|max:1000',
            'images_mobile.*'       => 'nullable',
            'image_links.*'         => 'nullable|string|max:500',
        ]);

        $banner = Banner::create([
            'title'         => $validated['title'],
            'link_url'      => $validated['link_url'] ?? null,
            'type'          => $validated['type'],
            'target_type'   => $validated['type'] == 3 ? 'brand' : ($validated['type'] == 4 ? 'category' : null),
            'target_id'     => in_array($validated['type'], [3, 4]) ? $validated['target_id'] : null,
            'device_flag'   => $validated['device_flag'],
            'placement_size'=> $validated['placement_size'],
            'content_type'  => 1,
            'sort_order'    => $validated['sort_order'] ?? 0,
            'is_active'     => $request->has('is_active'),
        ]);

        $uploadDisk = config('filesystems.disks.s3.bucket') ? 's3' : 'public';

        // Save uploaded images or text URLs
        $webFiles    = $request->file('images_web', []);
        $webInputs   = $request->input('images_web', []);
        $webUrlsText = $request->input('images_web_url_text', []);
        $mobileFiles = $request->file('images_mobile', []);
        $mobileInputs= $request->input('images_mobile', []);
        $imageLinks  = $request->input('image_links', []);
        
        $count = max(
            is_array($webFiles) ? count($webFiles) : 0,
            is_array($webInputs) ? count($webInputs) : 0,
            is_array($webUrlsText) ? count($webUrlsText) : 0,
            is_array($mobileFiles) ? count($mobileFiles) : 0,
            is_array($mobileInputs) ? count($mobileInputs) : 0
        );

        for ($index = 0; $index < $count; $index++) {
            $webFile = $webFiles[$index] ?? null;
            $webInput = $webInputs[$index] ?? null;
            $webText = $webUrlsText[$index] ?? null;

            $webUrl = null;
            if ($webFile instanceof \Illuminate\Http\UploadedFile) {
                $webUrl = $webFile->store('banners', $uploadDisk);
            } elseif (!empty($webInput) && is_string($webInput)) {
                $webUrl = $webInput;
            } elseif (!empty($webText) && is_string($webText)) {
                $webUrl = $webText;
            }

            $mobileFile = $mobileFiles[$index] ?? null;
            $mobileInput = $mobileInputs[$index] ?? null;
            $mobileUrl  = null;
            if ($mobileFile instanceof \Illuminate\Http\UploadedFile) {
                $mobileUrl = $mobileFile->store('banners', $uploadDisk);
            } elseif (!empty($mobileInput) && is_string($mobileInput)) {
                $mobileUrl = $mobileInput;
            }

            if (!$webUrl && !$mobileUrl) continue;

            BannerImage::create([
                'banner_id'      => $banner->id,
                'image_web_url'  => $webUrl,
                'image_mobile_url' => $mobileUrl,
                'link_url'       => $imageLinks[$index] ?? null,
                'sort_order'     => $index,
            ]);
        }

        return redirect()->route('content.banners.index')->with('success', 'Banner created successfully.');
    }

    public function edit($id)
    {
        $banner = Banner::with('images')->findOrFail($id);
        $brands = \App\Models\Product\Brand::where('deleted', false)->orderBy('name')->get();
        $categories = \App\Models\Product\Category::where('deleted', false)->orderBy('name')->get();
        return view('pages.banners.edit', compact('banner', 'brands', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);

        $validated = $request->validate([
            'title'           => 'required|string|max:255',
            'link_url'        => 'nullable|string|max:500',
            'type'            => 'required|integer|in:1,3,4',
            'target_id'       => 'nullable|string',
            'device_flag'     => 'required|integer|in:1,2,3',
            'placement_size'  => 'required|integer|in:1,2,3',
            'sort_order'      => 'nullable|integer|min:0',
            'is_active'       => 'boolean',
            'images_web.*'          => 'nullable',
            'images_web_url_text.*' => 'nullable|string|max:1000',
            'images_mobile.*'       => 'nullable',
            'image_links.*'         => 'nullable|string|max:500',
        ]);

        $banner->update([
            'title'         => $validated['title'],
            'link_url'      => $validated['link_url'] ?? null,
            'type'          => $validated['type'],
            'target_type'   => $validated['type'] == 3 ? 'brand' : ($validated['type'] == 4 ? 'category' : null),
            'target_id'     => in_array($validated['type'], [3, 4]) ? $validated['target_id'] : null,
            'device_flag'   => $validated['device_flag'],
            'placement_size'=> $validated['placement_size'],
            'sort_order'    => $validated['sort_order'] ?? 0,
            'is_active'     => $request->has('is_active'),
        ]);

        $uploadDisk = config('filesystems.disks.s3.bucket') ? 's3' : 'public';

        // Append new uploaded images or text URLs
        $webFiles    = $request->file('images_web', []);
        $webInputs   = $request->input('images_web', []);
        $webUrlsText = $request->input('images_web_url_text', []);
        $mobileFiles = $request->file('images_mobile', []);
        $mobileInputs= $request->input('images_mobile', []);
        $imageLinks  = $request->input('image_links', []);
        $existingCount = $banner->images()->count();

        $count = max(
            is_array($webFiles) ? count($webFiles) : 0,
            is_array($webInputs) ? count($webInputs) : 0,
            is_array($webUrlsText) ? count($webUrlsText) : 0,
            is_array($mobileFiles) ? count($mobileFiles) : 0,
            is_array($mobileInputs) ? count($mobileInputs) : 0
        );

        for ($index = 0; $index < $count; $index++) {
            $webFile = $webFiles[$index] ?? null;
            $webInput = $webInputs[$index] ?? null;
            $webText = $webUrlsText[$index] ?? null;

            $webUrl = null;
            if ($webFile instanceof \Illuminate\Http\UploadedFile) {
                $webUrl = $webFile->store('banners', $uploadDisk);
            } elseif (!empty($webInput) && is_string($webInput)) {
                $webUrl = $webInput;
            } elseif (!empty($webText) && is_string($webText)) {
                $webUrl = $webText;
            }

            $mobileFile = $mobileFiles[$index] ?? null;
            $mobileInput = $mobileInputs[$index] ?? null;
            $mobileUrl  = null;
            if ($mobileFile instanceof \Illuminate\Http\UploadedFile) {
                $mobileUrl = $mobileFile->store('banners', $uploadDisk);
            } elseif (!empty($mobileInput) && is_string($mobileInput)) {
                $mobileUrl = $mobileInput;
            }

            if (!$webUrl && !$mobileUrl) continue;

            BannerImage::create([
                'banner_id'        => $banner->id,
                'image_web_url'    => $webUrl,
                'image_mobile_url' => $mobileUrl,
                'link_url'         => $imageLinks[$index] ?? null,
                'sort_order'       => $existingCount + $index,
            ]);
        }

        // Handle deletions of existing images
        $deleteIds = $request->input('delete_images', []);
        foreach ($deleteIds as $imgId) {
            $img = BannerImage::find($imgId);
            if ($img && $img->banner_id === $banner->id) {
                if ($img->image_web_url) unlink_media($img->image_web_url);
                if ($img->image_mobile_url) unlink_media($img->image_mobile_url);
                $img->delete();
            }
        }

        return redirect()->route('content.banners.index')->with('success', 'Banner updated successfully.');
    }

    public function destroy($id)
    {
        $banner = Banner::with('images')->findOrFail($id);

        foreach ($banner->images as $img) {
            if ($img->image_web_url) unlink_media($img->image_web_url);
            if ($img->image_mobile_url) unlink_media($img->image_mobile_url);
            $img->delete();
        }

        if ($banner->image_web_url) unlink_media($banner->image_web_url);
        if ($banner->image_mobile_url) unlink_media($banner->image_mobile_url);
        $banner->delete();

        return redirect()->route('content.banners.index')->with('success', 'Banner deleted successfully.');
    }
}
