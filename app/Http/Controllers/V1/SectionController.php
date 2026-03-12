<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Section\AssignSectionProductRequest;
use App\Http\Requests\Section\UpdateSectionProductRequest;
use App\Models\SectionProduct;
use Exception;
use Illuminate\Http\JsonResponse;

class SectionController extends Controller
{
    private const VALID_SECTIONS = ['best_sellers', 'new_arrivals'];

    private const MAX_PRODUCTS_PER_SECTION = 8;

    // -------------------------------------------------------------------------
    // List All Sections
    // -------------------------------------------------------------------------

    public function index(): JsonResponse
    {
        try {
            $sections = [];

            foreach (self::VALID_SECTIONS as $sectionName) {
                $sections[] = [
                    'section_name' => $sectionName,
                    'products'     => SectionProduct::with('product')
                        ->where('section_name', $sectionName)
                        ->orderBy('sort_order')
                        ->get(),
                ];
            }

            return response()->json([
                'message' => 'Sections retrieved successfully.',
                'data'    => $sections,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve sections.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // -------------------------------------------------------------------------
    // Show Single Section
    // -------------------------------------------------------------------------

    public function show(string $section): JsonResponse
    {
        try {
            if (! in_array($section, self::VALID_SECTIONS)) {
                return response()->json([
                    'message' => 'Invalid section. Valid sections are: ' . implode(', ', self::VALID_SECTIONS) . '.',
                ], 422);
            }

            $sectionProducts = SectionProduct::with('product')
                ->where('section_name', $section)
                ->orderBy('sort_order')
                ->get();

            return response()->json([
                'message' => 'Section retrieved successfully.',
                'data'    => [
                    'section_name' => $section,
                    'products'     => $sectionProducts,
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve section.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // -------------------------------------------------------------------------
    // Assign Products to Section (bulk)
    // -------------------------------------------------------------------------

    public function assignProduct(AssignSectionProductRequest $request, string $section): JsonResponse
    {
        try {
            if (! in_array($section, self::VALID_SECTIONS)) {
                return response()->json([
                    'message' => 'Invalid section. Valid sections are: ' . implode(', ', self::VALID_SECTIONS) . '.',
                ], 422);
            }

            $data       = $request->validated();
            $productIds = $data['product_id'];
            $sortOrders = $data['sort_order'] ?? [];

            $currentCount = SectionProduct::where('section_name', $section)->count();
            $available    = self::MAX_PRODUCTS_PER_SECTION - $currentCount;

            if (count($productIds) > $available) {
                return response()->json([
                    'message' => "Cannot assign " . count($productIds) . " products. Section '{$section}' has {$currentCount} product(s) and allows a maximum of " . self::MAX_PRODUCTS_PER_SECTION . ".",
                ], 422);
            }

            $now     = now();
            $created = [];

            foreach ($productIds as $index => $productId) {
                $sectionProduct               = new SectionProduct();
                $sectionProduct->section_name = $section;
                $sectionProduct->product_id   = $productId;
                $sectionProduct->sort_order   = $sortOrders[$index] ?? $index;
                $sectionProduct->created_at   = $now;
                $sectionProduct->save();

                $created[] = $sectionProduct->load('product');
            }

            return response()->json([
                'message' => count($created) . ' product(s) assigned to section successfully.',
                'data'    => $created,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to assign products to section.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // -------------------------------------------------------------------------
    // Update Section Product (sort_order)
    // -------------------------------------------------------------------------

    public function updateProduct(UpdateSectionProductRequest $request, string $section, string $id): JsonResponse
    {
        try {
            if (! in_array($section, self::VALID_SECTIONS)) {
                return response()->json([
                    'message' => 'Invalid section. Valid sections are: ' . implode(', ', self::VALID_SECTIONS) . '.',
                ], 422);
            }

            $sectionProduct = SectionProduct::where('section_name', $section)->findOrFail($id);
            $sectionProduct->update($request->validated());

            return response()->json([
                'message' => 'Section product updated successfully.',
                'data'    => $sectionProduct->load('product'),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to update section product.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // -------------------------------------------------------------------------
    // Remove Product from Section
    // -------------------------------------------------------------------------

    public function removeProduct(string $section, string $id): JsonResponse
    {
        try {
            if (! in_array($section, self::VALID_SECTIONS)) {
                return response()->json([
                    'message' => 'Invalid section. Valid sections are: ' . implode(', ', self::VALID_SECTIONS) . '.',
                ], 422);
            }

            $sectionProduct = SectionProduct::where('section_name', $section)->findOrFail($id);
            $sectionProduct->delete();

            return response()->json([
                'message' => 'Product removed from section successfully.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to remove product from section.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
