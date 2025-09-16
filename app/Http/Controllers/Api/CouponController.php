<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function validateCode(Request $request)
    {
        $data = $request->validate(['code' => ['required', 'string']]);
        $coupon = Coupon::where('code', $data['code'])->first();

        if (!$coupon || !$coupon->isValid()) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired coupon'], 422);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'code' => $coupon->code,
                'discount_percent' => $coupon->discount_percent,
                'valid_from' => $coupon->valid_from,
                'valid_to' => $coupon->valid_to,
                'usage_left' => max(0, $coupon->usage_limit - $coupon->used_count),
            ],
        ]);
    }
}

