<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index()
    {
        $now = now()->toDateString();
        $coupons = Coupon::whereDate('valid_from', '<=', $now)
            ->whereDate('valid_to', '>=', $now)
            ->orderByDesc('id')
            ->get([
                'id', 'code', 'title', 'description', 'discount_percent', 'discount_amount', 'valid_from', 'valid_to', 'usage_limit', 'used_count'
            ]);

        return response()->json([
            'success' => true,
            'data' => $coupons,
        ]);
    }

    public function show(Coupon $coupon)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'title' => $coupon->title,
                'description' => $coupon->description,
                'discount_percent' => $coupon->discount_percent,
                'discount_amount' => $coupon->discount_amount,
                'valid_from' => $coupon->valid_from,
                'valid_to' => $coupon->valid_to,
                'usage_left' => max(0, $coupon->usage_limit - $coupon->used_count),
            ],
        ]);
    }

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
                'title' => $coupon->title,
                'description' => $coupon->description,
                'discount_percent' => $coupon->discount_percent,
                'discount_amount' => $coupon->discount_amount,
                'valid_from' => $coupon->valid_from,
                'valid_to' => $coupon->valid_to,
                'usage_left' => max(0, $coupon->usage_limit - $coupon->used_count),
            ],
        ]);
    }
}
