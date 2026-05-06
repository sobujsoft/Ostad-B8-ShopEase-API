<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Customer::with('user:id,email,role,is_active,created_at')->latest();

            if ($search = $request->query('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            $customers = $query->paginate((int) $request->query('per_page', 10));

            return response()->json([
                'message' => 'Customers retrieved successfully.',
                'data'    => $customers,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve customers.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $customer = Customer::with('user:id,email,role,is_active,created_at')
                ->findOrFail($id);

            $orderStats = [];
            if ($customer->user_id) {
                $orders = $customer->user->orders();
                $orderStats = [
                    'total_orders'  => $orders->count(),
                    'total_spent'   => (float) $orders->sum('total'),
                    'last_order_at' => $orders->latest()->value('created_at'),
                ];
            }

            return response()->json([
                'message' => 'Customer retrieved successfully.',
                'data'    => [
                    'customer'    => $customer,
                    'order_stats' => $orderStats,
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Customer not found.',
                'error'   => $e->getMessage(),
            ], 404);
        }
    }

    public function toggleActive(string $id): JsonResponse
    {
        try {
            $customer            = Customer::findOrFail($id);
            $customer->is_active = ! $customer->is_active;
            $customer->save();

            if ($customer->user) {
                $customer->user->update(['is_active' => $customer->is_active]);
            }

            $status = $customer->is_active ? 'activated' : 'deactivated';

            return response()->json([
                'message' => "Customer {$status} successfully.",
                'data'    => $customer->load('user:id,email,role,is_active,created_at'),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to toggle customer status.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $customer = Customer::findOrFail($id);

            if ($customer->user) {
                $customer->user->update(['is_active' => false]);
            }

            $customer->delete();

            return response()->json([
                'message' => 'Customer deleted successfully.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to delete customer.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
