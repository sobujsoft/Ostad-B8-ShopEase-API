<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\HeroBanner\StoreHeroBannerRequest;
use App\Http\Requests\HeroBanner\UpdateHeroBannerRequest;
use App\Models\HeroBanner;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HeroBannerController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $banners = HeroBanner::latest()->paginate(10);

            return response()->json([
                'message' => 'Hero banners retrieved successfully.',
                'data'    => $banners,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve hero banners.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function store(StoreHeroBannerRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('banner_img')) {
                $extension = $request->file('banner_img')->getClientOriginalExtension();
                $filename = Str::uuid() . '.' . $extension;
                $data['banner_img'] = $request->file('banner_img')->storeAs('hero-banners', $filename, 'public');
            }

            $banner = HeroBanner::create($data);

            return response()->json([
                'message' => 'Hero banner created successfully.',
                'data'    => $banner,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to create hero banner.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $banner = HeroBanner::findOrFail($id);

            return response()->json([
                'message' => 'Hero banner retrieved successfully.',
                'data'    => $banner,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Hero banner not found.',
                'error'   => $e->getMessage(),
            ], 404);
        }
    }

    public function update(UpdateHeroBannerRequest $request, string $id): JsonResponse
    {
        try {
            $banner = HeroBanner::findOrFail($id);
            $data = $request->validated();
            $oldImage = $banner->banner_img;

            if ($request->hasFile('banner_img')) {
                $extension = $request->file('banner_img')->getClientOriginalExtension();
                $filename = Str::uuid() . '.' . $extension;
                $data['banner_img'] = $request->file('banner_img')->storeAs('hero-banners', $filename, 'public');

                if ($oldImage && Storage::disk('public')->exists($oldImage)) {
                    Storage::disk('public')->delete($oldImage);
                }
            }

            $banner->update($data);

            return response()->json([
                'message' => 'Hero banner updated successfully.',
                'data'    => $banner,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to update hero banner.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $banner = HeroBanner::findOrFail($id);

            if ($banner->banner_img && Storage::disk('public')->exists($banner->banner_img)) {
                Storage::disk('public')->delete($banner->banner_img);
            }

            $banner->delete();

            return response()->json([
                'message' => 'Hero banner deleted successfully.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to delete hero banner.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleActive(string $id): JsonResponse
    {
        try {
            $banner = HeroBanner::findOrFail($id);
            $banner->is_active = ! $banner->is_active;
            $banner->save();

            $status = $banner->is_active ? 'activated' : 'deactivated';

            return response()->json([
                'message' => "Hero banner {$status} successfully.",
                'data'    => $banner,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to toggle hero banner active status.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function storefront(): JsonResponse
    {
        try {
            $banners = HeroBanner::where('is_active', true)
                ->orderBy('sort_order')
                ->get();

            return response()->json([
                'message' => 'Hero banners retrieved successfully.',
                'data'    => $banners,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve hero banners.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
