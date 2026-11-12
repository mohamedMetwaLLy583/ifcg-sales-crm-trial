<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Banner;
use App\Models\ContentPage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ShippingZone;
use App\Models\User;
use App\Models\Wishlist;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StorefrontController extends Controller
{
    public function home()
    {
        try {
            $categoryIds = $this->catalogCategoryIds();

            $categories = Category::where('is_active', true)
                ->whereNull('parent_id')
                ->with('children')
                ->get();

            $featuredProducts = Product::where('is_active', true)
                ->whereIn('category_id', $categoryIds)
                ->latest()
                ->take(24)
                ->with(['images', 'category'])
                ->get();

            $catalogProducts = Product::where('is_active', true)
                ->whereIn('category_id', $categoryIds)
                ->with(['images', 'category'])
                ->latest()
                ->take(12)
                ->get();

            $dailyProducts = Product::where('is_active', true)
                ->whereIn('category_id', $categoryIds)
                ->with(['images', 'category'])
                ->orderBy('price')
                ->take(8)
                ->get();

            $offerProducts = Product::where('is_active', true)
                ->whereIn('category_id', $categoryIds)
                ->where(function ($query) {
                    $query->whereColumn('old_price', '>', 'price')
                        ->orWhere('discount', '>', 0)
                        ->orWhere('is_limited', true);
                })
                ->with(['images', 'category'])
                ->latest()
                ->take(8)
                ->get();

            $categorySections = Category::where('is_active', true)
                ->whereHas('products', fn ($query) => $query->where('is_active', true))
                ->with(['products' => fn ($query) => $query
                    ->where('is_active', true)
                    ->with(['images', 'category'])
                    ->latest()
                    ->take(8)])
                ->orderBy('name')
                ->take(8)
                ->get();

            $stats = [
                'active_products' => Product::where('is_active', true)->whereIn('category_id', $categoryIds)->count(),
                'categories' => Category::where('is_active', true)->count(),
                'delivered_orders' => Order::where('status', 'delivered')->count(),
                'rental_products' => Product::where('is_active', true)
                    ->whereHas('category', fn ($query) => $query->where('slug', 'mens-suits-rental'))
                    ->count(),
            ];
            $heroBanner = Banner::published()
                ->where('placement', 'home_hero')
                ->orderBy('sort_order')
                ->latest()
                ->first();
        } catch (\Throwable $e) {
            report($e);

            $categories = collect();
            $featuredProducts = collect();
            $catalogProducts = collect();
            $dailyProducts = collect();
            $offerProducts = collect();
            $categorySections = collect();
            $stats = [
                'active_products' => 0,
                'categories' => 0,
                'delivered_orders' => 0,
                'rental_products' => 0,
            ];
            $heroBanner = null;
        }

        return view('store.home', compact(
            'categories',
            'featuredProducts',
            'catalogProducts',
            'dailyProducts',
            'offerProducts',
            'categorySections',
            'stats',
            'heroBanner'
        ));
    }

    public function shop()
    {
        $sortBy = request('sort_by');
        $sortDirection = request('sort_direction', 'asc') === 'desc' ? 'desc' : 'asc';
        $allowedSorts = ['name', 'price', 'quantity', 'created_at'];

        try {
            $catalogCategoryIds = $this->catalogCategoryIds();

            $requestedCategoryId = request('category_id');
            $allowedCategoryId = $requestedCategoryId && $catalogCategoryIds->contains((int) $requestedCategoryId)
                ? (int) $requestedCategoryId
                : null;

            $products = Product::where('is_active', true)
                ->whereIn('category_id', $catalogCategoryIds)
                ->when(request('search'), fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
                ->when($allowedCategoryId, fn ($q, $id) => $q->where('category_id', $id))
                ->when(request('min_price'), fn ($q, $p) => $q->where('price', '>=', $p))
                ->when(request('max_price'), fn ($q, $p) => $q->where('price', '<=', $p))
                ->when(request('in_stock'), fn ($q) => $q->where('quantity', '>', 0))
                ->when(
                    $sortBy && in_array($sortBy, $allowedSorts, true),
                    fn ($q) => $q->orderBy($sortBy, $sortDirection),
                    fn ($q) => $q->latest()
                )
                ->with(['images', 'category'])
                ->paginate(12)
                ->appends(request()->query());

            $categories = Category::where('is_active', true)
                ->orderBy('name')
                ->get();
        } catch (\Throwable $e) {
            report($e);

            $products = new LengthAwarePaginator(
                collect(),
                0,
                12,
                LengthAwarePaginator::resolveCurrentPage(),
                ['path' => request()->url(), 'query' => request()->query()]
            );
            $categories = collect();
        }

        return view('store.shop', compact('products', 'categories'));
    }

    public function product(string $slug)
    {
        $product = Product::where('is_active', true)
            ->where(function ($query) use ($slug) {
                $query->where('slug', $slug);

                if (ctype_digit($slug)) {
                    $query->orWhere('id', (int) $slug);
                }
            })
            ->with([
                'images',
                'category',
                'variations' => fn ($q) => $q->where('is_active', true)
                    ->with(['attributeValues.attribute', 'attributeValues.option']),
            ])
            ->firstOrFail();

        $relatedProducts = Product::where('is_active', true)
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with(['images'])
            ->take(4)
            ->get();

        $inWishlist = auth()->check()
            ? auth()->user()->wishlists()->where('product_id', $product->id)->exists()
            : false;

        $variationOptions = $product->variations
            ->map(fn ($variation) => [
                'id' => $variation->id,
                'price' => (float) ($variation->price ?? $product->price),
                'sale_price' => $variation->sale_price !== null ? (float) $variation->sale_price : null,
                'stock' => (int) $variation->stock,
            ])
            ->values();

        return view('store.product', compact('product', 'relatedProducts', 'inWishlist', 'variationOptions'));
    }

    public function marketPrices(Request $request)
    {
        $categorySlug = Category::active()->where('slug', $request->query('category'))->exists()
            ? $request->query('category')
            : null;

        $categoryIds = $this->catalogCategoryIds();

        $products = Product::where('is_active', true)
            ->whereIn('category_id', $categoryIds)
            ->when($request->query('search'), fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))
            ->when($categorySlug, fn ($query, $slug) => $query->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('slug', $slug)))
            ->with(['category', 'images'])
            ->orderBy('name')
            ->paginate(24)
            ->appends($request->query());

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $marketProducts = Product::where('is_active', true)
            ->whereIn('category_id', $categoryIds)
            ->get(['attributes']);

        $lastImportedAt = $marketProducts
            ->map(fn ($product) => $product->attributes['market_imported_at'] ?? null)
            ->filter()
            ->sortDesc()
            ->first();

        $sourceDate = $marketProducts
            ->map(fn ($product) => $product->attributes['source_date'] ?? null)
            ->filter()
            ->first();

        return view('store.market-prices', compact('products', 'categories', 'lastImportedAt', 'sourceDate'));
    }

    private function catalogCategoryIds()
    {
        return Category::where('is_active', true)
            ->pluck('id');
    }

    public function cart()
    {
        return view('store.cart');
    }

    public function page(string $slug)
    {
        $page = ContentPage::active()
            ->where('slug', $slug)
            ->firstOrFail();

        return view('store.page', compact('page'));
    }

    public function checkout()
    {
        return view('store.checkout');
    }

    public function shippingCost(Request $request)
    {
        $city = trim((string) $request->query('city', ''));

        if ($city === '') {
            return response()->json([
                'cost' => 0,
                'zone' => null,
            ]);
        }

        $normalizedCity = Str::lower($city);
        $zone = ShippingZone::active()
            ->get()
            ->first(function (ShippingZone $zone) use ($normalizedCity) {
                return collect($zone->cities ?? [])
                    ->map(fn ($candidate) => Str::lower(trim((string) $candidate)))
                    ->contains($normalizedCity);
            });

        return response()->json([
            'cost' => $zone ? round((float) $zone->shipping_cost, 2) : 0,
            'zone' => $zone?->name,
        ]);
    }

    public function rentalAvailability(Request $request, Product $product)
    {
        $data = $request->validate([
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['nullable', 'date'],
            'variation_id' => ['nullable', 'integer'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        abort_unless($product->is_active, 404);

        $attributes = (array) ($product->attributes ?? []);
        $service = $attributes['service'] ?? 'sale';

        if (!in_array($service, ['rental', 'both'], true)) {
            return response()->json([
                'available' => false,
                'message' => 'هذا المنتج غير متاح للإيجار.',
            ], 422);
        }

        $startDate = $data['start_date'];
        $rentalDays = max(1, (int) ($attributes['rental_days'] ?? 1));
        $endDate = $data['end_date'] ?? Carbon::parse($startDate)->addDays($rentalDays)->toDateString();
        $quantity = (int) ($data['quantity'] ?? 1);
        $variationId = $data['variation_id'] ?? null;
        $availableStock = (int) $product->quantity;

        if ($variationId) {
            $variation = $product->variations()->where('id', $variationId)->where('is_active', true)->first();

            if (!$variation) {
                return response()->json([
                    'available' => false,
                    'message' => 'المتغير المطلوب غير متاح لهذا المنتج.',
                ], 422);
            }

            $availableStock = (int) $variation->stock;
        }

        $reserved = OrderItem::query()
            ->where('order_type', 'rental')
            ->whereNotIn('rental_status', ['returned', 'cancelled'])
            ->where('product_id', $product->id)
            ->when($variationId, fn ($query) => $query->where('product_variation_id', $variationId), fn ($query) => $query->whereNull('product_variation_id'))
            ->whereHas('order', fn ($query) => $query->whereNotIn('status', ['cancelled']))
            ->whereDate('rental_start_date', '<=', $endDate)
            ->whereDate('rental_end_date', '>=', $startDate)
            ->sum('quantity');

        $remaining = max(0, $availableStock - (int) $reserved);
        $available = $remaining >= $quantity;

        return response()->json([
            'available' => $available,
            'remaining' => $remaining,
            'reserved' => (int) $reserved,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'message' => $available
                ? 'البدلة متاحة في التاريخ المحدد.'
                : 'هذه البدلة غير متاحة للإيجار في التاريخ المحدد.',
        ]);
    }

    public function orderSuccess()
    {
        return view('store.order-success');
    }

    public function trackOrder()
    {
        return view('store.track-order');
    }

    public function trackOrderResult(Request $request)
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'max:100'],
        ], [
            'query.required' => 'اكتب رقم الطلب أو رقم الجوال.',
        ]);

        $query = trim($validated['query']);
        $orders = Order::where('order_number', $query)
            ->orWhere('customer_phone', $query)
            ->latest()
            ->get();

        $orders->each(function (Order $order) {
            $order->load([
                'items.product.images',
                'items.variation',
                'activities' => fn ($activityQuery) => $activityQuery->latest(),
            ]);
        });

        return view('store.track-order-result', compact('orders', 'query'));
    }

    public function showLogin()
    {
        return view('store.auth.login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'phone' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('phone', $validated['phone'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'phone' => 'رقم الجوال أو كلمة المرور غير صحيحة.',
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'phone' => 'هذا الحساب غير مفعل حالياً.',
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('store.account'));
    }

    public function showRegister()
    {
        return view('store.auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $this->generateLocalEmailFromPhone($validated['phone']),
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        try {
            if (!$user->hasRole('customer')) {
                $user->assignRole('customer');
            }
        } catch (\Throwable $e) {
            report($e);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('store.account')->with('success', 'تم إنشاء حسابك بنجاح.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('store.home');
    }

    public function account()
    {
        $user = auth()->user();
        $ordersQuery = Order::where('user_id', $user->id);
        $ordersCount = (clone $ordersQuery)->count();
        $purchasesTotal = (clone $ordersQuery)
            ->where('status', '!=', 'cancelled')
            ->sum('total');
        $wishlistCount = Wishlist::where('user_id', $user->id)->count();
        $recentOrders = (clone $ordersQuery)
            ->latest()
            ->take(3)
            ->get();

        return view('store.account', compact(
            'user',
            'recentOrders',
            'ordersCount',
            'purchasesTotal',
            'wishlistCount'
        ));
    }

    public function accountOrders()
    {
        $orders = Order::with(['items.product', 'items.variation'])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('store.account-orders', compact('orders'));
    }

    public function updateAccount(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50', 'unique:users,phone,' . $user->id],
            'address' => ['nullable', 'string', 'max:1000'],
        ]);

        $user->update($validated);

        return redirect()->route('store.account')->with('success', 'تم تحديث بيانات الحساب.');
    }

    public function wishlist()
    {
        $wishlists = Wishlist::with(['product.category', 'product.images', 'product.variations'])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(12);

        return view('store.wishlist', compact('wishlists'));
    }

    public function toggleWishlist(Product $product, Request $request)
    {
        if (!auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => false,
                    'message' => 'سجل الدخول أولاً لإضافة المنتج إلى المفضلة.',
                ], 401);
            }

            return redirect()->route('store.login');
        }

        $wishlist = Wishlist::where('user_id', auth()->id())
            ->where('product_id', $product->id)
            ->first();

        if ($wishlist) {
            $wishlist->delete();
            $active = false;
            $message = 'تم حذف المنتج من المفضلة.';
        } else {
            Wishlist::firstOrCreate([
                'user_id' => auth()->id(),
                'product_id' => $product->id,
            ]);
            $active = true;
            $message = 'تمت إضافة المنتج إلى المفضلة.';
        }

        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'active' => $active,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    private function generateLocalEmailFromPhone(string $phone): string
    {
        $normalized = preg_replace('/\D+/', '', $phone);

        if ($normalized === '') {
            $normalized = Str::slug($phone) ?: Str::random(10);
        }

        return $normalized . '@xstore.local';
    }
}
