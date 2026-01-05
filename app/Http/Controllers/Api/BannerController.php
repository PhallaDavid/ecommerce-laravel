<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index()
    {
        return response()->json([
            'message' => 'Banners retrieved successfully',
            'data' => Banner::all()
        ]);
    }

    public function show($id)
    {
        $banner = Banner::findOrFail($id);
        return response()->json([
            'message' => 'Banner retrieved successfully',
            'data' => $banner
        ]);
    }

public function store(Request $request)
{
    $request->validate([
        'title' => 'required|string',
        'images.*' => 'required|image|max:2048',
        'link' => 'nullable|string',
        'is_active' => 'nullable|boolean',
    ]);

    $imagePaths = [];

    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $image) {
            // Save file to 'public/banners' folder
            $path = $image->store('banners', 'public');

            // Convert to full URL
            $imagePaths[] = url(Storage::url($path));
        }
    }

    // Save banner record
    $banner = Banner::create([
        'title' => $request->title,
        'images' => $imagePaths,
        'link' => $request->link,
        'is_active' => $request->is_active ?? true,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Banner created successfully',
        'data' => $banner
    ], 201);
}

    // PUT /api/banners/{id} - update title/link/images
    public function update(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);

        $request->validate([
            'title' => 'sometimes|required|string',
            'images.*' => 'sometimes|image|max:2048',
            'link' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        // Handle new images
        $imagePaths = $banner->images ?? [];
        if ($request->hasFile('images')) {
            // Optionally delete old images
            foreach ($imagePaths as $old) {
                $path = parse_url($old, PHP_URL_PATH);
                $oldPath = str_replace('/storage/', '', $path);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('banners', 'public');
                $imagePaths[] = url(Storage::url($path));
            }
        }

        $banner->update([
            'title' => $request->title ?? $banner->title,
            'images' => $imagePaths,
            'link' => $request->link ?? $banner->link,
            'is_active' => $request->has('is_active') ? $request->is_active : $banner->is_active,
        ]);

        return response()->json($banner);
    }

    // DELETE /api/banners/{id}
    public function destroy($id)
    {
        $banner = Banner::findOrFail($id);

        // Delete images from storage
        if ($banner->images) {
            foreach ($banner->images as $img) {
                $path = parse_url($img, PHP_URL_PATH);
                $storagePath = str_replace('/storage/', '', $path);
                if (Storage::disk('public')->exists($storagePath)) {
                    Storage::disk('public')->delete($storagePath);
                }
            }
        }

        $banner->delete();

        return response()->json(['message' => 'Banner deleted successfully']);
    }
}
