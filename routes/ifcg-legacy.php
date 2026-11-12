<?php

use App\Http\Controllers\dashboard\CustomerController;
use App\Http\Controllers\dashboard\BannerController;
use App\Http\Controllers\dashboard\ContentPageController;
use App\Http\Controllers\dashboard\CouponController;
use App\Http\Controllers\dashboard\RoleController;
use App\Http\Controllers\dashboard\UserController;
use App\Http\Controllers\dashboard\CategoryController;
use App\Http\Controllers\dashboard\OrderController;
use App\Http\Controllers\dashboard\PaymentController;
use App\Http\Controllers\dashboard\PlaceholderController;
use App\Http\Controllers\dashboard\ProductController;
use App\Http\Controllers\dashboard\ProductVariationController;
use App\Http\Controllers\dashboard\ProfileController;
use App\Http\Controllers\dashboard\ReviewController;
use App\Http\Controllers\dashboard\InventoryController;
use App\Http\Controllers\dashboard\ReturnRequestController;
use App\Http\Controllers\dashboard\ReportController;
use App\Http\Controllers\dashboard\MarketPriceController;
use App\Http\Controllers\dashboard\ShippingZoneController;
use App\Http\Controllers\dashboard\StoreSettingController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\dashboard\DashboardController;
use Illuminate\Support\Facades\Route;



// dashboard routes 
Route::middleware(['auth', \App\Http\Middleware\CrmStaff::class, 'admin.access'])->group(function () {
    Route::get('/legacy/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::resource('users', UserController::class);
    Route::post('users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');

    Route::resource('categories', CategoryController::class)
        ->names('dashboard.categories')
        ->except(['show']);
    Route::resource('products', ProductController::class)->except(['show']);
    Route::resource('products.variations', ProductVariationController::class)
        ->only(['index', 'store', 'update', 'destroy']);
    Route::patch('products/{product}/images/{image}/primary', [ProductController::class, 'setPrimaryImage'])
        ->name('products.images.primary');
    Route::delete('products/{product}/images/{image}', [ProductController::class, 'destroyImage'])
        ->name('products.images.destroy');
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
    Route::patch('orders/{order}', [OrderController::class, 'update'])->name('orders.update');
    Route::get('payments', [PaymentController::class, 'index'])->name('dashboard.payments');
    Route::patch('payments/{payment}', [PaymentController::class, 'update'])->name('dashboard.payments.update');
    Route::get('payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('dashboard.payments.receipt');
    Route::get('returns', [ReturnRequestController::class, 'index'])->name('dashboard.returns');
    Route::post('returns', [ReturnRequestController::class, 'store'])->name('dashboard.returns.store');
    Route::patch('returns/{returnRequest}', [ReturnRequestController::class, 'update'])->name('dashboard.returns.update');
    Route::get('coupons', [CouponController::class, 'index'])->name('dashboard.coupons');
    Route::post('coupons', [CouponController::class, 'store'])->name('dashboard.coupons.store');
    Route::patch('coupons/{coupon}', [CouponController::class, 'update'])->name('dashboard.coupons.update');
    Route::delete('coupons/{coupon}', [CouponController::class, 'destroy'])->name('dashboard.coupons.destroy');
    Route::get('banners', [BannerController::class, 'index'])->name('dashboard.banners');
    Route::post('banners', [BannerController::class, 'store'])->name('dashboard.banners.store');
    Route::patch('banners/{banner}', [BannerController::class, 'update'])->name('dashboard.banners.update');
    Route::delete('banners/{banner}', [BannerController::class, 'destroy'])->name('dashboard.banners.destroy');
    Route::get('pages', [ContentPageController::class, 'index'])->name('dashboard.pages');
    Route::post('pages', [ContentPageController::class, 'store'])->name('dashboard.pages.store');
    Route::patch('pages/{page}', [ContentPageController::class, 'update'])->name('dashboard.pages.update');
    Route::delete('pages/{page}', [ContentPageController::class, 'destroy'])->name('dashboard.pages.destroy');
    Route::get('reviews', [ReviewController::class, 'index'])->name('dashboard.reviews');
    Route::patch('reviews/{review}/approve', [ReviewController::class, 'approve'])->name('dashboard.reviews.approve');
    Route::patch('reviews/{review}/hide', [ReviewController::class, 'hide'])->name('dashboard.reviews.hide');
    Route::delete('reviews/{review}', [ReviewController::class, 'destroy'])->name('dashboard.reviews.destroy');

    Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::patch('/customers/{user}/activate', [CustomerController::class, 'activate'])
        ->name('customers.activate');

    Route::patch('/customers/{user}/deactivate', [CustomerController::class, 'deactivate'])
        ->name('customers.deactivate');

    Route::delete('/customers/{user}', [CustomerController::class, 'destroy'])
        ->name('customers.destroy');
    Route::resource('roles', RoleController::class);

    Route::get('/settings/store', [StoreSettingController::class, 'index'])->name('dashboard.settings.store');
    Route::post('/settings/store', [StoreSettingController::class, 'update'])->name('dashboard.settings.store.update');
    Route::get('/settings/checkout', [StoreSettingController::class, 'checkout'])->name('dashboard.settings.checkout');
    Route::post('/settings/checkout', [StoreSettingController::class, 'updateCheckout'])->name('dashboard.settings.checkout.update');
    Route::resource('shipping', ShippingZoneController::class)
        ->except(['show'])
        ->parameters(['shipping' => 'zone']);
    Route::get('reports/sales', [ReportController::class, 'sales'])
        ->middleware('can:manage_order')
        ->name('dashboard.reports.sales');
    Route::get('reports/stock', [ReportController::class, 'stock'])
        ->middleware('can:manage_order')
        ->name('dashboard.reports.stock');
    Route::get('inventory', [InventoryController::class, 'index'])->name('dashboard.inventory');
    Route::post('inventory/adjust', [InventoryController::class, 'adjust'])->name('dashboard.inventory.adjust');

    $placeholderPages = [
        ['abandoned-carts', 'dashboard.abandoned-carts', 'السلات المتروكة', 'متابعة السلات غير المكتملة واستعادتها بحملات تسويقية.', 'ti ti-shopping-cart-off'],
    ];

    foreach ($placeholderPages as [$path, $name, $title, $description, $icon]) {
        Route::get($path, [PlaceholderController::class, 'show'])
            ->name($name)
            ->defaults('title', $title)
            ->defaults('description', $description)
            ->defaults('icon', $icon);
    }
});






