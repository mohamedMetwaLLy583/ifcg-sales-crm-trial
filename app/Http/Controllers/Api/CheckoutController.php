<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\ShippingZone;
use App\Models\StoreSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class CheckoutController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['required', 'string', 'max:2000'],
            'city' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'payment_method' => ['nullable', 'in:cash_on_delivery,whatsapp,vodafone_cash,instapay'],
            'payment_reference' => ['nullable', 'required_if:payment_method,vodafone_cash', 'required_if:payment_method,instapay', 'string', 'max:120'],
            'payment_receipt' => ['nullable', 'required_if:payment_method,vodafone_cash', 'required_if:payment_method,instapay', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.variation_id' => ['nullable', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.order_type' => ['nullable', 'in:sale,rental'],
            'items.*.rental_start_date' => ['nullable', 'date', 'after_or_equal:today'],
            'items.*.rental_end_date' => ['nullable', 'date'],
        ], [
            'payment_reference.required_if' => 'أدخل رقم العملية بعد التحويل.',
            'payment_receipt.required_if' => 'ارفع صورة إيصال التحويل قبل تأكيد الطلب.',
            'payment_receipt.image' => 'يجب أن يكون إيصال التحويل صورة.',
            'payment_receipt.mimes' => 'صيغة الإيصال يجب أن تكون JPG أو PNG أو WEBP.',
            'payment_receipt.max' => 'حجم صورة الإيصال يجب ألا يتجاوز 2 ميجابايت.',
        ]);

        $paymentMethod = $validated['payment_method'] ?? 'cash_on_delivery';
        if ($paymentMethod === 'vodafone_cash' && !StoreSetting::get('vodafone_cash_number')) {
            return response()->json(['status' => false, 'message' => 'الدفع عبر فودافون كاش غير مفعّل حالياً.'], 422);
        }
        if ($paymentMethod === 'instapay' && !StoreSetting::get('instapay_address')) {
            return response()->json(['status' => false, 'message' => 'الدفع عبر إنستاباي غير مفعّل حالياً.'], 422);
        }

        $receiptPath = null;

        try {
            $order = DB::transaction(function () use ($validated, $request, &$receiptPath) {
                $user = $this->resolveCustomer($request, $validated);
                $totals = $this->prepareItems($validated['items']);
                $couponResult = $this->resolveCoupon($validated['coupon_code'] ?? null, $totals['total']);
                $shippingTotal = $this->resolveShippingTotal($validated['city'] ?? null);
                $orderTotal = round(max(0, $totals['total'] - $couponResult['discount']) + $shippingTotal, 2);
                $orderNumber = $this->generateOrderNumber();

                $order = Order::create([
                    'user_id' => $user->id,
                    'order_number' => $orderNumber,
                    'customer_name' => $validated['name'],
                    'customer_phone' => $validated['phone'],
                    'customer_email' => $validated['email'] ?? null,
                    'shipping_address' => $validated['address'],
                    'shipping_city' => $validated['city'] ?? null,
                    'customer_notes' => $validated['notes'] ?? null,
                    'total' => $orderTotal,
                    'shipping_total' => $shippingTotal,
                    'tax_total' => 0,
                    'status' => 'pending',
                    'payment_status' => 'unpaid',
                    'payment_method' => $validated['payment_method'] ?? 'cash_on_delivery',
                    'coupon_id' => $couponResult['coupon']?->id,
                    'coupon_code' => $couponResult['coupon']?->code,
                    'discount_total' => $couponResult['discount'],
                ]);

                foreach ($totals['items'] as $item) {
                    $order->items()->create([
                        'product_id' => $item['product']->id,
                        'product_variation_id' => $item['variation']?->id,
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'subtotal' => $item['subtotal'],
                        'status' => 'pending',
                        'order_type' => $item['order_type'],
                        'rental_start_date' => $item['rental_start_date'],
                        'rental_end_date' => $item['rental_end_date'],
                        'rental_days' => $item['rental_days'],
                        'deposit_amount' => $item['deposit_amount'],
                        'rental_status' => $item['order_type'] === 'rental' ? 'reserved' : null,
                        'deposit_status' => $item['order_type'] === 'rental'
                            ? ((float) $item['deposit_amount'] > 0 ? 'held' : 'none')
                            : 'none',
                    ]);

                    if ($item['order_type'] === 'sale') {
                        if ($item['variation']) {
                            $item['variation']->decrement('stock', $item['quantity']);
                        } else {
                            $item['product']->decrement('quantity', $item['quantity']);
                        }
                    }
                }

                if (in_array($order->payment_method, ['vodafone_cash', 'instapay'], true)) {
                    $receiptPath = $validated['payment_receipt']->store('payments/receipts', 'local');
                    if (!$receiptPath) {
                        throw new \RuntimeException('Unable to store payment receipt.');
                    }
                }

                Payment::create([
                    'order_id' => $order->id,
                    'amount' => $order->total,
                    'provider' => $order->payment_method,
                    'transaction_id' => $validated['payment_reference'] ?? null,
                    'status' => 'pending',
                    'provider_data' => [
                        'source' => 'checkout_api',
                        'manual_transfer' => in_array($order->payment_method, ['vodafone_cash', 'instapay'], true),
                        'recipient' => match ($order->payment_method) {
                            'vodafone_cash' => StoreSetting::get('vodafone_cash_number'),
                            'instapay' => StoreSetting::get('instapay_address'),
                            default => null,
                        },
                        'receipt_path' => $receiptPath,
                    ],
                ]);

                if ($couponResult['coupon']) {
                    $couponResult['coupon']->increment('used_count');
                }

                return $order->load(['items.product', 'items.variation', 'payment']);
            });

            return response()->json([
                'status' => true,
                'message' => 'تم إنشاء الطلب بنجاح',
                'data' => [
                    'order' => $this->formatOrder($order),
                ],
            ], 201);
        } catch (\InvalidArgumentException $e) {
            if ($receiptPath) {
                Storage::disk('local')->delete($receiptPath);
            }

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            if ($receiptPath) {
                Storage::disk('local')->delete($receiptPath);
            }

            return response()->json([
                'status' => false,
                'message' => 'تعذر إنشاء الطلب',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    private function resolveCustomer(Request $request, array $data): User
    {
        try {
            if ($request->bearerToken()) {
                $user = JWTAuth::parseToken()->authenticate();

                if ($user) {
                    return $user;
                }
            }
        } catch (\Throwable $e) {
            // Guest checkout continues when the token is missing or invalid.
        }

        $existingCustomer = User::where('phone', $data['phone'])->first();

        if ($existingCustomer) {
            $existingCustomer->update([
                'name' => $existingCustomer->name ?: $data['name'],
                'address' => $data['address'],
                'city' => $data['city'] ?? $existingCustomer->city,
                'is_active' => true,
            ]);

            try {
                if (!$existingCustomer->hasRole('customer')) {
                    $existingCustomer->assignRole('customer');
                }
            } catch (\Throwable $e) {
                // Checkout should continue even if roles are unavailable.
            }

            return $existingCustomer;
        }

        $guestKey = sha1(($data['email'] ?? '') . '|' . $data['phone']);
        $email = 'guest-' . $guestKey . '@guest.local';

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $data['name'],
                'phone' => $data['phone'],
                'address' => $data['address'],
                'city' => $data['city'] ?? null,
                'password' => Hash::make(Str::random(32)),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        try {
            if (!$user->hasRole('customer')) {
                $user->assignRole('customer');
            }
        } catch (\Throwable $e) {
            // Guest checkout should not fail if roles are not seeded yet.
        }

        return $user;
    }

    private function prepareItems(array $items): array
    {
        $prepared = [];
        $total = 0;

        foreach ($items as $item) {
            $product = Product::where('id', $item['product_id'])
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            if (!$product) {
                throw new \InvalidArgumentException('المنتج غير متاح');
            }

            $quantity = (int) $item['quantity'];
            $variation = null;
            $attributes = (array) ($product->attributes ?? []);
            $service = $attributes['service'] ?? 'sale';
            $orderType = ($item['order_type'] ?? null) === 'rental' ? 'rental' : 'sale';

            if ($orderType === 'rental' && !in_array($service, ['rental', 'both'], true)) {
                throw new \InvalidArgumentException("هذا المنتج غير متاح للإيجار: {$product->name}");
            }

            if ($orderType === 'sale' && $service === 'rental') {
                $orderType = 'rental';
            }

            $price = $orderType === 'rental'
                ? (float) ($attributes['rental_price'] ?? $product->price)
                : (float) $product->price;
            $available = (int) $product->quantity;

            if (!empty($item['variation_id'])) {
                $variation = ProductVariation::where('id', $item['variation_id'])
                    ->where('product_id', $product->id)
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->first();

                if (!$variation) {
                    throw new \InvalidArgumentException('المتغير المطلوب غير متاح لهذا المنتج');
                }

                $price = $orderType === 'rental'
                    ? (float) ($attributes['rental_price'] ?? $variation->price ?? $product->price)
                    : (float) ($variation->price ?? $product->price);
                $available = (int) $variation->stock;
            }

            if ($available < $quantity) {
                throw new \InvalidArgumentException("الكمية المطلوبة غير متاحة للمنتج: {$product->name}");
            }

            $rentalStartDate = null;
            $rentalEndDate = null;
            $rentalDays = null;
            $depositAmount = 0;

            if ($orderType === 'rental') {
                $rentalStartDate = $item['rental_start_date'] ?? now()->toDateString();
                $rentalDays = max(1, (int) ($attributes['rental_days'] ?? 1));
                $rentalEndDate = $item['rental_end_date'] ?? Carbon::parse($rentalStartDate)->addDays($rentalDays)->toDateString();

                if (Carbon::parse($rentalEndDate)->lt(Carbon::parse($rentalStartDate))) {
                    throw new \InvalidArgumentException("تاريخ نهاية الإيجار غير صحيح للمنتج: {$product->name}");
                }

                $this->assertRentalAvailability($product, $variation, $quantity, $rentalStartDate, $rentalEndDate);

                $depositAmount = round((float) ($attributes['deposit'] ?? 0) * $quantity, 2);
            }

            $subtotal = round(($price * $quantity) + $depositAmount, 2);
            $total += $subtotal;

            $prepared[] = [
                'product' => $product,
                'variation' => $variation,
                'quantity' => $quantity,
                'price' => $price,
                'subtotal' => $subtotal,
                'order_type' => $orderType,
                'rental_start_date' => $rentalStartDate,
                'rental_end_date' => $rentalEndDate,
                'rental_days' => $rentalDays,
                'deposit_amount' => $depositAmount,
            ];
        }

        return [
            'items' => $prepared,
            'total' => round($total, 2),
        ];
    }

    private function assertRentalAvailability(Product $product, ?ProductVariation $variation, int $quantity, string $startDate, string $endDate): void
    {
        $available = $variation ? (int) $variation->stock : (int) $product->quantity;

        $reserved = OrderItem::query()
            ->where('order_type', 'rental')
            ->whereNotIn('rental_status', ['returned', 'cancelled'])
            ->where('product_id', $product->id)
            ->when(
                $variation,
                fn ($query) => $query->where('product_variation_id', $variation->id),
                fn ($query) => $query->whereNull('product_variation_id')
            )
            ->whereHas('order', fn ($query) => $query->whereNotIn('status', ['cancelled']))
            ->whereDate('rental_start_date', '<=', $endDate)
            ->whereDate('rental_end_date', '>=', $startDate)
            ->sum('quantity');

        if (($reserved + $quantity) > $available) {
            throw new \InvalidArgumentException("هذه البدلة غير متاحة للإيجار في التاريخ المحدد: {$product->name}");
        }
    }

    private function generateOrderNumber(): string
    {
        $prefix = 'BR-' . now()->format('Ymd') . '-';
        $lastOrder = Order::where('order_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->first();

        $next = $lastOrder
            ? ((int) Str::afterLast($lastOrder->order_number, '-')) + 1
            : 1;

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    private function resolveCoupon(?string $code, float $itemsTotal): array
    {
        $code = Coupon::normalizeCode((string) $code);

        if ($code === '') {
            return [
                'coupon' => null,
                'discount' => 0.0,
            ];
        }

        $coupon = Coupon::where('code', $code)->lockForUpdate()->first();

        if (!$coupon || !$coupon->isUsableFor($itemsTotal)) {
            throw new \InvalidArgumentException('كود الخصم غير صالح أو غير متاح لهذا الطلب');
        }

        return [
            'coupon' => $coupon,
            'discount' => $coupon->discountFor($itemsTotal),
        ];
    }

    private function resolveShippingTotal(?string $city): float
    {
        $city = strtolower(trim((string) $city));

        if ($city === '') {
            return 0.0;
        }

        $zone = ShippingZone::active()
            ->get()
            ->first(function (ShippingZone $zone) use ($city) {
                return collect($zone->cities ?? [])
                    ->map(fn ($storedCity) => strtolower(trim((string) $storedCity)))
                    ->contains($city);
            });

        return $zone ? (float) $zone->shipping_cost : 0.0;
    }

    private function formatOrder(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'payment_method' => $order->payment_method,
            'shipping_total' => (float) $order->shipping_total,
            'discount_total' => (float) ($order->discount_total ?? 0),
            'coupon_code' => $order->coupon_code,
            'total' => (float) $order->total,
            'customer' => [
                'name' => $order->customer_name,
                'phone' => $order->customer_phone,
                'email' => $order->customer_email,
                'address' => $order->shipping_address,
                'city' => $order->shipping_city,
            ],
            'items' => $order->items->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'variation_id' => $item->product_variation_id,
                    'name' => $item->product?->name,
                    'quantity' => $item->quantity,
                    'price' => (float) $item->price,
                    'subtotal' => (float) $item->subtotal,
                    'order_type' => $item->order_type ?? 'sale',
                    'rental_start_date' => $item->rental_start_date?->toDateString(),
                    'rental_end_date' => $item->rental_end_date?->toDateString(),
                    'rental_days' => $item->rental_days,
                    'deposit_amount' => (float) ($item->deposit_amount ?? 0),
                ];
            })->values(),
            'created_at' => $order->created_at?->toDateTimeString(),
        ];
    }
}
