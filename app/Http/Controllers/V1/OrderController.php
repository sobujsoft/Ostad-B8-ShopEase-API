<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\PlaceOrderRequest;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderStatusLog;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function placeOrder(PlaceOrderRequest $request): JsonResponse
    {
        try {
            $user      = $request->user();
            $cartItems = Cart::with('product')->where('user_id', $user->id)->get();

            if ($cartItems->isEmpty()) {
                return response()->json([
                    'message' => 'Your cart is empty.',
                ], 422);
            }

            $outOfStock = $cartItems->filter(fn ($item) => $item->product->stock_status !== 'in_stock');
            if ($outOfStock->isNotEmpty()) {
                return response()->json([
                    'message' => 'Some products are out of stock.',
                    'data'    => $outOfStock->pluck('product.name'),
                ], 422);
            }

            $data = $request->validated();

            $subtotal    = $cartItems->sum(fn ($item) => $item->unit_price * $item->quantity);
            $shippingFee = 0;
            $total       = $subtotal + $shippingFee;

            $order = DB::transaction(function () use ($user, $data, $cartItems, $subtotal, $shippingFee, $total) {
                $order = Order::create([
                    'user_id'          => $user->id,
                    'order_number'     => 'ORD-' . strtoupper(Str::random(10)),
                    'status'           => 'pending',
                    'subtotal'         => $subtotal,
                    'shipping_fee'     => $shippingFee,
                    'discount_amount'  => 0,
                    'total'            => $total,
                    'payment_method'   => 'cod',
                    'payment_status'   => 'pending',
                    'customer_name'    => $data['customer_name'],
                    'customer_email'   => $data['customer_email'],
                    'customer_phone'   => $data['customer_phone'],
                    'shipping_address' => ['address' => $data['shipping_address']],
                ]);

                foreach ($cartItems as $cartItem) {
                    $order->items()->create([
                        'product_id'      => $cartItem->product_id,
                        'product_name'    => $cartItem->product->name,
                        'product_code'    => $cartItem->product->code,
                        'variant_details' => [
                            'color' => $cartItem->product->color,
                            'size'  => $cartItem->product->size,
                        ],
                        'quantity'        => $cartItem->quantity,
                        'unit_price'      => $cartItem->unit_price,
                        'total_price'     => $cartItem->unit_price * $cartItem->quantity,
                    ]);
                }

                OrderStatusLog::create([
                    'order_id'    => $order->id,
                    'from_status' => null,
                    'to_status'   => 'pending',
                    'changed_by'  => $user->id,
                    'note'        => 'Order placed via Cash on Delivery.',
                ]);

                Cart::where('user_id', $user->id)->delete();

                return $order;
            });

            return response()->json([
                'message' => 'Order placed successfully.',
                'data'    => $order->load('items'),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to place order.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function myOrders(Request $request): JsonResponse
    {
        try {
            $orders = Order::with('items')
                ->where('user_id', $request->user()->id)
                ->latest()
                ->paginate(10);

            return response()->json([
                'message' => 'Orders retrieved successfully.',
                'data'    => $orders,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve orders.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function myOrderShow(Request $request, string $orderNumber): JsonResponse
    {
        try {
            $order = Order::with(['items.product.images', 'statusLogs'])
                ->where('user_id', $request->user()->id)
                ->where('order_number', $orderNumber)
                ->firstOrFail();

            return response()->json([
                'message' => 'Order retrieved successfully.',
                'data'    => $order,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Order not found.',
                'error'   => $e->getMessage(),
            ], 404);
        }
    }
}
