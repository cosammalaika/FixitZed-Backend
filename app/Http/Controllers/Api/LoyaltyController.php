<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Loyalty;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoyaltyController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $points = (int) $user->loyalty_points;
        $value = Loyalty::maxRedeemableValue($points);
        $threshold = Loyalty::threshold();

        return response()->json([
            'success' => true,
            'data' => [
                'points' => $points,
                'value' => $value,
                'point_value' => Loyalty::pointValue(),
                'earn_divisor' => Loyalty::earnDivisor(),
                'threshold' => $threshold,
                'eligible' => $points >= $threshold,
            ],
        ]);
    }
}
