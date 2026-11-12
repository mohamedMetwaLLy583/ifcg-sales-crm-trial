<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function validateCoupon(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'items_total' => ['required', 'numeric', 'min:0'],
        ]);

        $code = Coupon::normalizeCode($validated['code']);
        $coupon = Coupon::where('code', $code)->first();
        $itemsTotal = (float) $validated['items_total'];

        if (!$coupon || !$coupon->isUsableFor($itemsTotal)) {
            return response()->json([
                'status' => false,
                'message' => 'كود الخصم غير صالح أو غير متاح لهذا الطلب',
            ], 422);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'code' => $coupon->code,
                'type' => $coupon->type,
                'value' => (float) $coupon->value,
                'discount_total' => $coupon->discountFor($itemsTotal),
            ],
        ]);
    }
}
