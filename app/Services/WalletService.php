<?php

namespace App\Services;

use App\Models\Fixer;
use App\Models\FixerSubscription;
use App\Models\FixerWallet;
use App\Models\SubscriptionPlan;
use App\Models\WalletLedger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WalletService
{
    /**
     * Credits a fixer's wallet with the plan coins and updates status/expiry.
     */
    public function approveSubscriptionAndCredit(FixerSubscription $subscription): void
    {
        DB::transaction(function () use ($subscription) {
            $subscription->refresh();
            if ($subscription->status === 'approved') {
                return; // idempotent
            }

            /** @var SubscriptionPlan $plan */
            $plan = $subscription->plan()->firstOrFail();

            $subscription->status = 'approved';
            $subscription->coins_awarded = $plan->coins;
            $subscription->starts_at = now();
            $subscription->expires_at = $plan->valid_days ? now()->addDays($plan->valid_days) : null;
            $subscription->save();

            /** @var FixerWallet $wallet */
            $wallet = FixerWallet::lockForUpdate()->firstOrCreate(
                ['fixer_id' => $subscription->fixer_id],
                ['coin_balance' => 0, 'subscription_status' => 'pending']
            );

            $wallet->coin_balance += (int) $plan->coins;
            $wallet->last_subscription_expires_at = $subscription->expires_at;
            $wallet->subscription_status = $this->computeStatus($wallet->coin_balance, $subscription->expires_at);
            $wallet->save();

            WalletLedger::create([
                'fixer_id' => $subscription->fixer_id,
                'delta' => (int) $plan->coins,
                'reason' => 'purchase',
                'meta' => ['subscription_id' => $subscription->id, 'plan_id' => $plan->id],
            ]);
        });
    }

    /**
     * Deducts 1 coin when fixer accepts a service request. Throws ValidationException on failure.
     */
    public function deductOnAccept(int $fixerId, int $serviceRequestId): void
    {
        DB::transaction(function () use ($fixerId, $serviceRequestId) {
            /** @var FixerWallet $wallet */
            $wallet = FixerWallet::where('fixer_id', $fixerId)->lockForUpdate()->first();
            if (! $wallet) {
                throw ValidationException::withMessages([
                    'wallet' => ['Insufficient coins. Please buy a plan.'],
                ]);
            }

            $expires = $wallet->last_subscription_expires_at;
            $active = $this->isActive($wallet->coin_balance, $expires);
            if (! $active || $wallet->coin_balance < 1) {
                throw ValidationException::withMessages([
                    'wallet' => ['Insufficient coins. Please buy a plan.'],
                ]);
            }

            $wallet->coin_balance = $wallet->coin_balance - 1;
            $wallet->subscription_status = $this->computeStatus($wallet->coin_balance, $expires);
            $wallet->save();

            WalletLedger::create([
                'fixer_id' => $fixerId,
                'delta' => -1,
                'reason' => 'service_request_accept',
                'meta' => ['service_request_id' => $serviceRequestId],
            ]);
        });
    }

    public function isActive(int $coins, $expiresAt): bool
    {
        if ($coins <= 0) return false;
        if ($expiresAt === null) return true;
        return now()->lessThanOrEqualTo($expiresAt);
    }

    public function computeStatus(int $coins, $expiresAt): string
    {
        return $this->isActive($coins, $expiresAt) ? 'approved' : 'pending';
    }
}

