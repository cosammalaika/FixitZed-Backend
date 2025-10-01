<?php

namespace App\Support;

use App\Models\User;

class Loyalty
{
    /**
     * Points earned per kwacha spent. e.g. earn 1 point per 10 ZMW.
     */
    protected const EARN_DIVISOR = 10; // amount / 10

    /**
     * Monetary value of a single point in kwacha.
     */
    protected const POINT_VALUE = 1; // 1 point = 1 ZMW

    protected const REDEEM_THRESHOLD = 50; // minimum points before highlighting redemption

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

        return $points * static::POINT_VALUE;
    }

    public static function pointsForValue(float $value): int
    {
        if ($value <= 0) {
            return 0;
        }

        return (int) floor($value / static::POINT_VALUE);
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
        return static::REDEEM_THRESHOLD;
    }

    public static function pointValue(): float
    {
        return static::POINT_VALUE;
    }

    public static function earnDivisor(): int
    {
        return static::EARN_DIVISOR;
    }
}
