<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusLog;
use HasinHayder\Sslcommerz\Facades\Sslcommerz;
use Illuminate\Http\Request;

class SslcommerzController extends Controller
{
    public function success(Request $request)
    {
        $tranId = $request->input('tran_id');
        $order  = Order::where('order_number', $tranId)->first();

        if (! $order) {
            return $this->redirectToFrontend('failed', $tranId, 'Order not found.');
        }

        $isValid = Sslcommerz::validatePayment(
            $request->all(),
            $tranId,
            (float) $order->total
        );

        if ($isValid) {
            if ($order->payment_status !== 'paid') {
                $order->update([
                    'payment_status' => 'paid',
                    'status'         => 'confirmed',
                ]);

                OrderStatusLog::create([
                    'order_id'    => $order->id,
                    'from_status' => 'pending',
                    'to_status'   => 'confirmed',
                    'changed_by'  => $order->user_id,
                    'note'        => 'Payment completed via SSLCommerz.',
                ]);
            }

            return $this->redirectToFrontend('success', $tranId);
        }

        return $this->redirectToFrontend('failed', $tranId, 'Payment validation failed.');
    }

    public function failure(Request $request)
    {
        $tranId = $request->input('tran_id');
        $order  = Order::where('order_number', $tranId)->first();

        if ($order && $order->payment_status !== 'paid') {
            $order->update(['payment_status' => 'failed']);
        }

        return $this->redirectToFrontend('failed', $tranId, 'Payment failed.');
    }

    public function cancel(Request $request)
    {
        $tranId = $request->input('tran_id');
        $order  = Order::where('order_number', $tranId)->first();

        if ($order && $order->payment_status !== 'paid') {
            $order->update(['payment_status' => 'cancelled']);
        }

        return $this->redirectToFrontend('cancelled', $tranId, 'Payment was cancelled.');
    }

    public function ipn(Request $request)
    {
        $tranId = $request->input('tran_id');
        $order  = Order::where('order_number', $tranId)->first();

        if (! $order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        if (! Sslcommerz::verifyHash($request->all())) {
            return response()->json(['message' => 'Invalid hash.'], 400);
        }

        $isValid = Sslcommerz::validatePayment(
            $request->all(),
            $tranId,
            (float) $order->total
        );

        if ($isValid && $order->payment_status !== 'paid') {
            $order->update([
                'payment_status' => 'paid',
                'status'         => 'confirmed',
            ]);

            OrderStatusLog::create([
                'order_id'    => $order->id,
                'from_status' => 'pending',
                'to_status'   => 'confirmed',
                'changed_by'  => $order->user_id,
                'note'        => 'Payment confirmed via SSLCommerz IPN.',
            ]);
        }

        return response()->json(['message' => 'IPN processed.']);
    }

    private function redirectToFrontend(string $status, ?string $tranId, ?string $message = null): \Illuminate\Http\RedirectResponse
    {
        $frontendUrl = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000'));
        $query       = http_build_query(array_filter([
            'status'       => $status,
            'order_number' => $tranId,
            'message'      => $message,
        ]));

        return redirect("{$frontendUrl}/payment/result?{$query}");
    }
}
