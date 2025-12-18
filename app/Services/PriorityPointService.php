<?php

namespace App\Services;

use App\Models\Fixer;
use App\Models\Setting;
use Illuminate\Support\Facades\Schema;

class PriorityPointService
{
    public const DEFAULT_BASE = 100;
    public const DEFAULT_CAP = 200;
    public const DEFAULT_FLOOR = 0;

    public const REASON_ASSIGNMENT = 'assignment';
    public const REASON_COMPLETION = 'completion';
    public const REASON_TIMEOUT = 'timeout';
    public const REASON_OFFER = 'offer';
    public const REASON_WEEKLY_RECOVERY = 'weekly_recovery';
    public const REASON_IDLE_BONUS = 'idle_bonus';
    public const REASON_MANUAL = 'manual';
    public const REASON_DORMANCY_GUARD = 'dormancy_guard';

    protected bool $hasPriorityColumn;
    protected bool $hasLowSinceColumn;

    public function __construct()
    {
        $this->hasPriorityColumn = Schema::hasColumn('fixers', 'priority_points');
        $this->hasLowSinceColumn = Schema::hasColumn('fixers', 'priority_low_since_at');
    }

    public function adjust(
        Fixer $fixer,
        int $delta,
        string $reason,
        array $meta = [],
        ?int $performedBy = null,
        ?int $floor = null,
        ?int $cap = null
    ): int {
        $current = $this->currentPoints($fixer);

        $cap = $cap ?? (int) Setting::get('priority.cap', self::DEFAULT_CAP);
        $floor = $floor ?? (int) Setting::get('priority.floor', self::DEFAULT_FLOOR);

        $next = max($floor, min($cap, $current + $delta));

        $this->store($fixer, $next);

        if ($this->hasLowSinceColumn) {
            if ($next <= $floor) {
                $fixer->forceFill(['priority_low_since_at' => now()])->saveQuietly();
            } elseif ($fixer->priority_low_since_at) {
                $fixer->forceFill(['priority_low_since_at' => null])->saveQuietly();
            }
        }

        return $next;
    }

    public function manualAdjust(
        Fixer $fixer,
        int $delta,
        string $reason = self::REASON_MANUAL,
        array $meta = [],
        ?int $performedBy = null,
        ?int $floor = null,
        ?int $cap = null
    ): int {
        return $this->adjust($fixer, $delta, $reason, $meta, $performedBy, $floor, $cap);
    }

    public function onAssignment(Fixer $fixer, array $meta = []): int
    {
        $delta = (int) Setting::get('priority.assignment_penalty', 0);
        if ($delta < 0) {
            $delta = 0;
        }

        return $this->adjust($fixer, $delta, self::REASON_ASSIGNMENT, $meta);
    }

    public function onCompletion(Fixer $fixer, array $meta = []): int
    {
        $bonus = (int) Setting::get('priority.completion_bonus', 10);
        if ($bonus <= 0) {
            $bonus = 10;
        }

        return $this->adjust($fixer, $bonus, self::REASON_COMPLETION, $meta);
    }

    public function onTimeout(Fixer $fixer, array $meta = []): int
    {
        $penalty = (int) Setting::get('priority.timeout_penalty', -10);
        if ($penalty > 0) {
            $penalty = -abs($penalty);
        } elseif ($penalty == 0) {
            $penalty = -10;
        }

        return $this->adjust($fixer, $penalty, self::REASON_TIMEOUT, $meta);
    }

    public function onOffer(Fixer $fixer, array $meta = []): int
    {
        return $this->adjust($fixer, 0, self::REASON_OFFER, $meta);
    }

    public function onWeeklyRecovery(Fixer $fixer, array $meta = []): int
    {
        $bonus = (int) Setting::get('priority.weekly_recovery', 5);
        return $this->adjust($fixer, $bonus, self::REASON_WEEKLY_RECOVERY, $meta);
    }

    public function onIdleBonus(Fixer $fixer, array $meta = []): int
    {
        $bonus = (int) Setting::get('priority.idle_bonus', 4);
        return $this->adjust($fixer, $bonus, self::REASON_IDLE_BONUS, $meta);
    }

    public function compositeScore(Fixer $fixer, array $context = []): float
    {
        $priority = $this->currentPoints($fixer);
        $distance = max(0.0, (float) ($context['distance_km'] ?? 0));
        $rating = max(0.0, (float) ($context['rating'] ?? $fixer->rating_avg ?? 0));
        $acceptRate = max(0.0, min(1.0, (float) ($context['accept_rate'] ?? 0)));

        $distanceScore = $distance > 0 ? 1 / (1 + $distance) : 1;

        return ($priority * 1.0)
            + ($rating * 10.0)
            + ($acceptRate * 40.0)
            + ($distanceScore * 20.0);
    }

    protected function currentPoints(Fixer $fixer): int
    {
        $value = $fixer->getAttribute('priority_points');

        if ($value === null) {
            return self::DEFAULT_BASE;
        }

        return (int) $value;
    }

    protected function store(Fixer $fixer, int $points): void
    {
        if ($this->hasPriorityColumn) {
            $fixer->forceFill(['priority_points' => $points])->saveQuietly();
            return;
        }

        $fixer->priority_points = $points;
    }
}
