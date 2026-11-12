<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ReturnRequest;
use App\Models\Coupon;
use App\Models\Banner;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    public function index()
    {
        // إجمالي المستخدمين (excluding admin & customer)
        $totalUsers = User::whereDoesntHave('roles', function ($q) {
            $q->whereIn('name', ['admin', 'customer']);
        })->count();

        // إجمالي العملاء (role = customer)
        $totalCustomers = User::whereHas('roles', function ($q) {
            $q->where('name', 'customer');
        })->count();

        $totalCategories = Category::count();
        $totalProducts = Product::count();
        $totalOrders = Order::count();
        $todayOrders = Order::whereDate('created_at', today())->count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $confirmedOrders = Order::where('status', 'confirmed')->count();
        $shippedOrders = Order::where('status', 'shipped')->count();
        $totalSales = Order::where('status', 'delivered')->sum('total');
        $thisMonthSales = Order::where('status', 'delivered')
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('total');
        $lastMonthSales = Order::where('status', 'delivered')
            ->whereBetween('created_at', [
                now()->subMonthNoOverflow()->startOfMonth(),
                now()->subMonthNoOverflow()->endOfMonth(),
            ])
            ->sum('total');
        $salesGrowth = $lastMonthSales > 0
            ? (($thisMonthSales - $lastMonthSales) / $lastMonthSales) * 100
            : ($thisMonthSales > 0 ? 100 : 0);
        $latestOrders = Order::latest()->limit(8)->get();
        $lowStockProductsCount = Product::where('is_active', true)
            ->where('quantity', '<=', 5)
            ->count();
        $lowStockProducts = Product::where('is_active', true)
            ->where('quantity', '<=', 5)
            ->orderBy('quantity')
            ->limit(8)
            ->get();
        $pendingPaymentsAmount = Payment::where('status', 'pending')->sum('amount');
        $pendingReturnsCount = ReturnRequest::where('status', 'requested')->count();
        $activeCouponsCount = Coupon::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->count();
        $publishedBannersCount = Banner::published()->count();

        return view('dashboard', compact(
            'totalUsers',
            'totalCustomers',
            'totalCategories',
            'totalProducts',
            'totalOrders',
            'todayOrders',
            'pendingOrders',
            'confirmedOrders',
            'shippedOrders',
            'totalSales',
            'thisMonthSales',
            'lastMonthSales',
            'salesGrowth',
            'latestOrders',
            'lowStockProductsCount',
            'lowStockProducts',
            'pendingPaymentsAmount',
            'pendingReturnsCount',
            'activeCouponsCount',
            'publishedBannersCount'
        ));
    }
}
