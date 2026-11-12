<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function sales(Request $request)
    {
        $validated = $request->validate([
            'period' => ['nullable', 'in:today,week,month,year'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        [$dateFrom, $dateTo, $period] = $this->resolveDateRange($validated);
        $baseOrders = $this->deliveredOrdersBetween($dateFrom, $dateTo);

        $totalSales = (clone $baseOrders)->sum('total');
        $completedOrdersCount = (clone $baseOrders)->count();
        $averageOrderValue = $completedOrdersCount > 0 ? $totalSales / $completedOrdersCount : 0;
        $shippingTotal = (clone $baseOrders)->sum('shipping_total');

        $dailySales = (clone $baseOrders)
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('SUM(total) as total_sales')
            ->selectRaw('COUNT(*) as orders_count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $topProducts = OrderItem::query()
            ->select('product_id')
            ->selectRaw('SUM(quantity) as sold_quantity')
            ->selectRaw('SUM(subtotal) as revenue')
            ->whereHas('order', function ($query) use ($dateFrom, $dateTo) {
                $query->where('status', 'delivered')
                    ->whereBetween('created_at', [$dateFrom->copy()->startOfDay(), $dateTo->copy()->endOfDay()]);
            })
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('sold_quantity')
            ->limit(10)
            ->get();

        $salesByPaymentMethod = (clone $baseOrders)
            ->selectRaw("COALESCE(payment_method, 'unknown') as payment_method")
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw('SUM(total) as total_sales')
            ->groupBy('payment_method')
            ->orderByDesc('total_sales')
            ->get();

        return view('dashboard.reports.sales', compact(
            'period',
            'dateFrom',
            'dateTo',
            'totalSales',
            'completedOrdersCount',
            'averageOrderValue',
            'shippingTotal',
            'dailySales',
            'topProducts',
            'salesByPaymentMethod'
        ));
    }

    public function stock(Request $request)
    {
        $validated = $request->validate([
            'stock_status' => ['nullable', 'in:all,out,low,ok'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $stockStatus = $validated['stock_status'] ?? 'all';
        $categories = Category::orderBy('name')->get();

        $summary = [
            'total' => Product::where('is_active', true)->count(),
            'out' => Product::where('is_active', true)->where('quantity', '<=', 0)->count(),
            'low' => Product::where('is_active', true)->where('quantity', '>', 0)->where('quantity', '<=', 5)->count(),
            'ok' => Product::where('is_active', true)->where('quantity', '>', 5)->count(),
        ];

        $soldQuantitySubquery = OrderItem::query()
            ->selectRaw('COALESCE(SUM(quantity), 0)')
            ->whereColumn('order_items.product_id', 'products.id');

        $lastOrderSubquery = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->selectRaw('MAX(orders.created_at)')
            ->whereColumn('order_items.product_id', 'products.id');

        $products = Product::query()
            ->select('products.*')
            ->selectSub($soldQuantitySubquery, 'sold_quantity')
            ->selectSub($lastOrderSubquery, 'last_order_at')
            ->with('category')
            ->where('is_active', true)
            ->when($stockStatus === 'out', fn ($query) => $query->where('quantity', '<=', 0))
            ->when($stockStatus === 'low', fn ($query) => $query->where('quantity', '>', 0)->where('quantity', '<=', 5))
            ->when($stockStatus === 'ok', fn ($query) => $query->where('quantity', '>', 5))
            ->when(!empty($validated['category_id']), fn ($query) => $query->where('category_id', $validated['category_id']))
            ->when(!empty($validated['search']), function ($query) use ($validated) {
                $search = $validated['search'];

                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->orderBy('quantity')
            ->paginate(20)
            ->withQueryString();

        return view('dashboard.reports.stock', compact(
            'categories',
            'products',
            'summary',
            'stockStatus'
        ));
    }

    private function resolveDateRange(array $filters): array
    {
        $period = $filters['period'] ?? 'month';

        if (!empty($filters['date_from']) || !empty($filters['date_to'])) {
            $dateFrom = !empty($filters['date_from'])
                ? Carbon::parse($filters['date_from'])
                : Carbon::now()->startOfMonth();
            $dateTo = !empty($filters['date_to'])
                ? Carbon::parse($filters['date_to'])
                : Carbon::now();

            return [$dateFrom, $dateTo, $period];
        }

        return match ($period) {
            'today' => [Carbon::today(), Carbon::today(), $period],
            'week' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek(), $period],
            'year' => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear(), $period],
            default => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth(), 'month'],
        };
    }

    private function deliveredOrdersBetween(Carbon $dateFrom, Carbon $dateTo)
    {
        return Order::query()
            ->where('status', 'delivered')
            ->whereBetween('created_at', [$dateFrom->copy()->startOfDay(), $dateTo->copy()->endOfDay()]);
    }
}
