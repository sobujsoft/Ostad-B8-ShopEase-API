<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\UpdateCartRequest;
use App\Models\Cart;
use App\Models\Product;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $cartItems = Cart::with('product.images')
                ->where('user_id', $request->user()->id)
                ->latest()
                ->get();

            $summary = [
                'total_items' => $cartItems->sum('quantity'),
                'subtotal'    => $cartItems->sum(fn ($item) => $item->unit_price * $item->quantity),
            ];

            return response()->json([
                'message' => 'Cart retrieved successfully.',
                'data'    => [
                    'items'   => $cartItems,
                    'summary' => $summary,
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve cart.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function store(AddToCartRequest $request): JsonResponse
    {
        try {
            $data    = $request->validated();
            $product = Product::where('is_active', true)->findOrFail($data['product_id']);

            if ($product->stock_status !== 'in_stock') {
                return response()->json([
                    'message' => 'Product is out of stock.',
                ], 422);
            }

            $existingItem = Cart::where('user_id', $request->user()->id)
                ->where('product_id', $product->id)
                ->first();

            if ($existingItem) {
                $existingItem->quantity += $data['quantity'] ?? 1;
                $existingItem->save();
                $cartItem = $existingItem->load('product.images');
            } else {
                $cartItem = Cart::create([
                    'user_id'    => $request->user()->id,
                    'product_id' => $product->id,
                    'quantity'   => $data['quantity'] ?? 1,
                    'unit_price' => $product->discount_price ?? $product->price,
                ]);
                $cartItem->load('product.images');
            }

            return response()->json([
                'message' => 'Product added to cart successfully.',
                'data'    => $cartItem,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to add product to cart.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateCartRequest $request, string $id): JsonResponse
    {
        try {
            $cartItem = Cart::where('user_id', $request->user()->id)->findOrFail($id);
            $cartItem->update($request->validated());

            return response()->json([
                'message' => 'Cart item updated successfully.',
                'data'    => $cartItem->load('product.images'),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to update cart item.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $cartItem = Cart::where('user_id', $request->user()->id)->findOrFail($id);
            $cartItem->delete();

            return response()->json([
                'message' => 'Cart item removed successfully.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to remove cart item.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function clear(Request $request): JsonResponse
    {
        try {
            Cart::where('user_id', $request->user()->id)->delete();

            return response()->json([
                'message' => 'Cart cleared successfully.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to clear cart.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
