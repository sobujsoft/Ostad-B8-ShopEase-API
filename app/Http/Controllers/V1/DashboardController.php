<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $now       = Carbon::now();
            $thisMonth = $now->copy()->startOfMonth();
            $lastMonth = $now->copy()->subMonth();

            $stats     = $this->getStatCards($thisMonth, $lastMonth);
            $recent    = $this->getRecentOrders();
            $topCats   = $this->getTopCategories();

            return response()->json([
                'message' => 'Dashboard data retrieved successfully.',
                'data'    => [
                    'stats'            => $stats,
                    'recent_orders'    => $recent,
                    'top_categories'   => $topCats,
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve dashboard data.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    private function getStatCards(Carbon $thisMonth, Carbon $lastMonth): array
    {
        $totalRevenue     = (float) Order::where('payment_status', 'paid')->sum('total');
        $lastMonthRevenue = (float) Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$lastMonth->copy()->startOfMonth(), $lastMonth->copy()->endOfMonth()])
            ->sum('total');

        $totalOrders     = Order::count();
        $lastMonthOrders = Order::whereBetween('created_at', [$lastMonth->copy()->startOfMonth(), $lastMonth->copy()->endOfMonth()])->count();

        $totalProducts     = Product::count();
        $lastMonthProducts = Product::where('created_at', '<=', $lastMonth->copy()->endOfMonth())->count();

        $totalCustomers     = Customer::count();
        $lastMonthCustomers = Customer::where('created_at', '<=', $lastMonth->copy()->endOfMonth())->count();

        return [
            'total_revenue' => [
                'value'      => $totalRevenue,
                'change'     => $this->percentChange($totalRevenue, $lastMonthRevenue),
            ],
            'total_orders' => [
                'value'      => $totalOrders,
                'change'     => $this->percentChange($totalOrders, $lastMonthOrders),
            ],
            'total_products' => [
                'value'      => $totalProducts,
                'change'     => $this->percentChange($totalProducts, $lastMonthProducts),
            ],
            'total_customers' => [
                'value'      => $totalCustomers,
                'change'     => $this->percentChange($totalCustomers, $lastMonthCustomers),
            ],
        ];
    }

    private function getRecentOrders(): array
    {
        return Order::select('id', 'order_number', 'customer_name', 'total', 'status', 'created_at')
            ->latest()
            ->limit(5)
            ->get()
            ->toArray();
    }

    private function getTopCategories(): array
    {
        return Category::where('is_active', true)
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->having('products_count', '>', 0)
            ->orderByDesc('products_count')
            ->limit(5)
            ->get()
            ->map(function ($cat) {
                $totalProducts = Product::where('is_active', true)->count();

                return [
                    'id'         => $cat->id,
                    'name'       => $cat->name,
                    'products'   => $cat->products_count,
                    'percentage' => $totalProducts > 0
                        ? round(($cat->products_count / $totalProducts) * 100)
                        : 0,
                ];
            })
            ->toArray();
    }

    private function percentChange(float $current, float $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
