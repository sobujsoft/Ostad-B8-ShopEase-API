<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductImageRequest;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductImageRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Product;
use App\Models\ProductImage;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    // -------------------------------------------------------------------------
    // Product CRUD
    // -------------------------------------------------------------------------

    public function index(): JsonResponse
    {
        try {
            $products = Product::with('images')->latest()->paginate(15);

            return response()->json([
                'message' => 'Products retrieved successfully.',
                'data'    => $products,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve products.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        try {
            $data         = $request->validated();
            $data['slug'] = Str::slug($data['name']);

            $product = Product::create($data);

            return response()->json([
                'message' => 'Product created successfully.',
                'data'    => $product->load('images'),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to create product.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $product = Product::with('images')->findOrFail($id);

            return response()->json([
                'message' => 'Product retrieved successfully.',
                'data'    => $product,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Product not found.',
                'error'   => $e->getMessage(),
            ], 404);
        }
    }

    public function update(UpdateProductRequest $request, string $id): JsonResponse
    {
        try {
            $product = Product::findOrFail($id);
            $data    = $request->validated();

            if (isset($data['name'])) {
                $data['slug'] = Str::slug($data['name']);
            }

            $product->update($data);

            return response()->json([
                'message' => 'Product updated successfully.',
                'data'    => $product->load('images'),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to update product.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $product = Product::with('images')->findOrFail($id);

            foreach ($product->images as $image) {
                if (Storage::disk('public')->exists($image->image_path)) {
                    Storage::disk('public')->delete($image->image_path);
                }
            }

            $product->delete();

            return response()->json([
                'message' => 'Product deleted successfully.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to delete product.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // -------------------------------------------------------------------------
    // Toggle Active / Stock
    // -------------------------------------------------------------------------

    public function toggleActive(string $id): JsonResponse
    {
        try {
            $product            = Product::findOrFail($id);
            $product->is_active = ! $product->is_active;
            $product->save();

            $status = $product->is_active ? 'activated' : 'deactivated';

            return response()->json([
                'message' => "Product {$status} successfully.",
                'data'    => $product,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to toggle product active status.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStock(string $id): JsonResponse
    {
        try {
            $product               = Product::findOrFail($id);
            $product->stock_status = $product->stock_status === 'in_stock' ? 'out_of_stock' : 'in_stock';
            $product->save();

            return response()->json([
                'message' => 'Product stock status updated successfully.',
                'data'    => $product,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to toggle product stock status.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // -------------------------------------------------------------------------
    // Product Image CRUD
    // -------------------------------------------------------------------------

    public function storeImage(StoreProductImageRequest $request, string $productId): JsonResponse
    {
        try {
            $product = Product::findOrFail($productId);
            $data    = $request->validated();

            $extension        = $request->file('image')->getClientOriginalExtension();
            $filename         = Str::uuid() . '.' . $extension;
            $data['image_path'] = $request->file('image')->storeAs(
                'products/' . $product->slug,
                $filename,
                'public'
            );

            if (! empty($data['is_primary'])) {
                $product->images()->update(['is_primary' => false]);
            }

            $image = $product->images()->create($data);

            return response()->json([
                'message' => 'Product image uploaded successfully.',
                'data'    => $image,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to upload product image.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function updateImage(UpdateProductImageRequest $request, string $productId, string $imageId): JsonResponse
    {
        try {
            $product = Product::findOrFail($productId);
            $image   = ProductImage::where('product_id', $product->id)->findOrFail($imageId);
            $data    = $request->validated();

            if ($request->hasFile('image')) {
                if (Storage::disk('public')->exists($image->image_path)) {
                    Storage::disk('public')->delete($image->image_path);
                }

                $extension        = $request->file('image')->getClientOriginalExtension();
                $filename         = Str::uuid() . '.' . $extension;
                $data['image_path'] = $request->file('image')->storeAs(
                    'products/' . $product->slug,
                    $filename,
                    'public'
                );
            }

            if (! empty($data['is_primary'])) {
                $product->images()->where('id', '!=', $image->id)->update(['is_primary' => false]);
            }

            $image->update($data);

            return response()->json([
                'message' => 'Product image updated successfully.',
                'data'    => $image,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to update product image.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function destroyImage(string $productId, string $imageId): JsonResponse
    {
        try {
            $product = Product::findOrFail($productId);
            $image   = ProductImage::where('product_id', $product->id)->findOrFail($imageId);

            if (Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }

            $image->delete();

            return response()->json([
                'message' => 'Product image deleted successfully.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to delete product image.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
