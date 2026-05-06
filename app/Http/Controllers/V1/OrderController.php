<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\PlaceOrderRequest;
use App\Http\Requests\Order\UpdateOrderStatusRequest;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderStatusLog;
use Exception;
use HasinHayder\Sslcommerz\Facades\Sslcommerz;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Order::query()
                ->withCount('items')
                ->latest();

            if ($status = $request->query('status')) {
                $query->where('status', $status);
            }

            if ($search = $request->query('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_email', 'like', "%{$search}%");
                });
            }

            if ($fromDate = $request->query('from_date')) {
                $query->whereDate('created_at', '>=', $fromDate);
            }

            if ($toDate = $request->query('to_date')) {
                $query->whereDate('created_at', '<=', $toDate);
            }

            $orders = $query->paginate((int) $request->query('per_page', 10));

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

    public function show(string $id): JsonResponse
    {
        try {
            $order = Order::with([
                'items.product.images',
                'statusLogs.changedBy:id,name,email',
            ])->findOrFail($id);

            return response()->json([
                'message' => 'Order retrieved successfully.',
                'data'    => [
                    'order'      => $order,
                    'item_total' => $order->items->sum('quantity'),
                    'totals'     => [
                        'subtotal'        => $order->subtotal,
                        'shipping_fee'    => $order->shipping_fee,
                        'discount_amount' => $order->discount_amount,
                        'grand_total'     => $order->total,
                    ],
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Order not found.',
                'error'   => $e->getMessage(),
            ], 404);
        }
    }

    public function updateStatus(UpdateOrderStatusRequest $request, string $id): JsonResponse
    {
        try {
            $order = Order::findOrFail($id);
            $data  = $request->validated();

            $updatedOrder = DB::transaction(function () use ($order, $data, $request) {
                $fromStatus = $order->status;
                $toStatus   = $data['status'];

                if ($fromStatus !== $toStatus) {
                    $order->status = $toStatus;

                    if (isset($data['admin_notes'])) {
                        $order->admin_notes = $data['admin_notes'];
                    }

                    $order->save();

                    OrderStatusLog::create([
                        'order_id'    => $order->id,
                        'from_status' => $fromStatus,
                        'to_status'   => $toStatus,
                        'changed_by'  => $request->user()->id,
                        'note'        => $data['note'] ?? null,
                    ]);
                } elseif (isset($data['admin_notes'])) {
                    $order->update([
                        'admin_notes' => $data['admin_notes'],
                    ]);
                }

                return $order->load('statusLogs.changedBy:id,name,email');
            });

            return response()->json([
                'message' => 'Order status updated successfully.',
                'data'    => $updatedOrder,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to update order status.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

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

            $paymentMethod = $data['payment_method'] ?? 'cod';
            $subtotal      = $cartItems->sum(fn ($item) => $item->unit_price * $item->quantity);
            $shippingFee   = 0;
            $total         = $subtotal + $shippingFee;

            $order = DB::transaction(function () use ($user, $data, $cartItems, $subtotal, $shippingFee, $total, $paymentMethod) {
                $order = Order::create([
                    'user_id'          => $user->id,
                    'order_number'     => 'ORD-' . strtoupper(Str::random(10)),
                    'status'           => 'pending',
                    'subtotal'         => $subtotal,
                    'shipping_fee'     => $shippingFee,
                    'discount_amount'  => 0,
                    'total'            => $total,
                    'payment_method'   => $paymentMethod,
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

                $note = $paymentMethod === 'sslcommerz'
                    ? 'Order placed. Awaiting SSLCommerz payment.'
                    : 'Order placed via Cash on Delivery.';

                OrderStatusLog::create([
                    'order_id'    => $order->id,
                    'from_status' => null,
                    'to_status'   => 'pending',
                    'changed_by'  => $user->id,
                    'note'        => $note,
                ]);

                Cart::where('user_id', $user->id)->delete();

                return $order;
            });

            if ($paymentMethod === 'sslcommerz') {
                return $this->initiateSslcommerzPayment($order);
            }

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

    private function initiateSslcommerzPayment(Order $order): JsonResponse
    {
        $productNames = $order->items->pluck('product_name')->implode(', ');
        $totalItems   = $order->items->sum('quantity');
        $address      = is_array($order->shipping_address)
            ? ($order->shipping_address['address'] ?? 'N/A')
            : $order->shipping_address;

        $response = Sslcommerz::setOrder(
            (float) $order->total,
            $order->order_number,
            $productNames
        )
            ->setCustomer(
                $order->customer_name,
                $order->customer_email,
                $order->customer_phone ?? ' '
            )
            ->setShippingInfo($totalItems, $address)
            ->makePayment();

        if ($response->success()) {
            return response()->json([
                'message'     => 'Payment initiated. Redirect to gateway.',
                'data'        => [
                    'order'        => $order->load('items'),
                    'payment_url'  => $response->gatewayPageURL(),
                    'session_key'  => $response->sessionKey(),
                ],
            ], 200);
        }

        $order->update(['payment_status' => 'failed']);

        return response()->json([
            'message' => 'Failed to initiate payment.',
            'error'   => $response->failedReason(),
        ], 500);
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
