<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $query = Coupon::query()->withCount('orders');

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('status')) {
            match ($request->input('status')) {
                'active' => $query->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
                    })
                    ->where(function ($q) {
                        $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                    }),
                'disabled' => $query->where('is_active', false),
                'expired' => $query->whereNotNull('expires_at')->where('expires_at', '<', now()),
                default => null,
            };
        }

        $coupons = $query->latest()->paginate(25)->withQueryString();

        $stats = [
            'total' => Coupon::count(),
            'active' => Coupon::where('is_active', true)
                ->where(function ($q) {
                    $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
                })
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                })
                ->count(),
            'used' => Coupon::sum('used_count'),
            'discounted_orders' => Coupon::whereHas('orders')->count(),
        ];

        return view('dashboard.coupons.index', [
            'coupons' => $coupons,
            'stats' => $stats,
            'typeLabels' => Coupon::TYPE_LABELS,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateCoupon($request);
        $validated['code'] = Coupon::normalizeCode($validated['code']);
        $validated['is_active'] = $request->boolean('is_active');

        Coupon::create($validated);

        return back()->with('success', 'تم إنشاء الكوبون بنجاح.');
    }

    public function update(Request $request, Coupon $coupon)
    {
        $validated = $this->validateCoupon($request, $coupon);
        $validated['code'] = Coupon::normalizeCode($validated['code']);
        $validated['is_active'] = $request->boolean('is_active');

        $coupon->update($validated);

        return back()->with('success', 'تم تحديث الكوبون بنجاح.');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->update(['is_active' => false]);

        return back()->with('success', 'تم تعطيل الكوبون بنجاح.');
    }

    private function validateCoupon(Request $request, ?Coupon $coupon = null): array
    {
        return $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('coupons', 'code')->ignore($coupon?->id),
            ],
            'name' => ['nullable', 'string', 'max:120'],
            'type' => ['required', Rule::in(Coupon::TYPES)],
            'value' => ['required', 'numeric', 'min:0.01'],
            'minimum_order_total' => ['nullable', 'numeric', 'min:0'],
            'max_discount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
