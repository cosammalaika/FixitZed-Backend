<?php

namespace App\Support;

use App\Models\Setting;
use App\Models\User;

class Loyalty
{
    /**
     * Points earned per kwacha spent. e.g. earn 1 point per 10 ZMW.
     */
    protected const EARN_DIVISOR = 10; // amount / 10 (fallback)

    /**
     * Monetary value of a single point in kwacha.
     */
    protected const POINT_VALUE = 1; // fallback: 1 point = 1 ZMW

    protected const REDEEM_THRESHOLD = 50; // fallback: minimum points (if settings missing)

    public static function earnForAmount(float $amount): int
    {
        if ($amount <= 0) {
            return 0;
        }

        return (int) floor($amount / static::EARN_DIVISOR);
    }

    public static function maxRedeemableValue(int $points): float
    {
        if ($points <= 0) {
            return 0.0;
        }

        return $points * static::pointValue();
    }

    public static function pointsForValue(float $value): int
    {
        if ($value <= 0) {
            return 0;
        }

        $pv = static::pointValue();
        return (int) floor($value / ($pv > 0 ? $pv : 1));
    }

    public static function applyRedemption(User $user, int $requestedPoints): int
    {
        $available = (int) $user->loyalty_points;
        $points = max(0, min($available, $requestedPoints));
        if ($points <= 0) {
            return 0;
        }

        $user->loyalty_points = $available - $points;
        $user->save();

        return $points;
    }

    public static function award(User $user, int $points): void
    {
        if ($points <= 0) {
            return;
        }

        $user->increment('loyalty_points', $points);
    }

    public static function threshold(): int
    {
        // Admin sets a monetary threshold (e.g., 50 ZMW) in general settings.
        // Convert to points based on point value.
        $thresholdValue = (float) Setting::get('loyalty.redeem_threshold_value', 50);
        $pv = static::pointValue();
        if ($pv <= 0) {
            $pv = 0.01; // sane default to avoid div/0
        }
        return (int) ceil($thresholdValue / $pv);
    }

    public static function pointValue(): float
    {
        // Admin-configured value: monetary value per 1 point, default K0.01
        $value = (float) Setting::get('loyalty.point_value', 0.01);
        return $value > 0 ? $value : 0.01;
    }

    public static function earnDivisor(): int
    {
        // Optional admin setting for earn rate; default 10.
        $div = (int) Setting::get('loyalty.earn_divisor', static::EARN_DIVISOR);
        return $div > 0 ? $div : static::EARN_DIVISOR;
    }
}
