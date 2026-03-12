<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Models\Category;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $categories = Category::latest()->paginate(15);

            return response()->json([
                'message' => 'Categories retrieved successfully.',
                'data'    => $categories,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve categories.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['slug'] = Str::slug($data['name']);

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('categories', 'public');
            }

            $category = Category::create($data);

            return response()->json([
                'message' => 'Category created successfully.',
                'data'    => $category,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to create category.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $category = Category::findOrFail($id);

            return response()->json([
                'message' => 'Category retrieved successfully.',
                'data'    => $category,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Category not found.',
                'error'   => $e->getMessage(),
            ], 404);
        }
    }

    public function update(UpdateCategoryRequest $request, string $id): JsonResponse
    {
        try {
            $category = Category::findOrFail($id);
            $data = $request->validated();

            if (isset($data['name'])) {
                $data['slug'] = Str::slug($data['name']);
            }

            if ($request->hasFile('image')) {
                if ($category->image) {
                    Storage::disk('public')->delete($category->image);
                }
                $data['image'] = $request->file('image')->store('categories', 'public');
            }

            $category->update($data);

            return response()->json([
                'message' => 'Category updated successfully.',
                'data'    => $category,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to update category.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $category = Category::findOrFail($id);

            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }

            $category->delete();

            return response()->json([
                'message' => 'Category deleted successfully.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to delete category.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
