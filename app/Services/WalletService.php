<?php

namespace App\Services;

use App\Models\Fixer;
use App\Models\FixerSubscription;
use App\Models\FixerWallet;
use App\Models\Notification;
use App\Models\SubscriptionPlan;
use App\Models\WalletLedger;
use App\Support\Loyalty;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class WalletService
{
    /**
     * Credits a fixer's wallet with the plan coins and updates status/expiry.
     */
    public function approveSubscriptionAndCredit(FixerSubscription $subscription): void
    {
        $context = DB::transaction(function () use ($subscription) {
            $subscription->refresh();
            $subscription->loadMissing(['plan', 'fixer.user']);
            if ($subscription->status === 'approved') {
                return null; // idempotent
            }

            /** @var SubscriptionPlan $plan */
            $plan = $subscription->plan()->firstOrFail();

            /** @var Fixer|null $fixer */
            $fixer = $subscription->fixer;
            $user = $fixer?->user;

            $supportsManualColumns = Schema::hasColumn('fixer_subscriptions', 'loyalty_deducted_at');

            if ($supportsManualColumns && $subscription->loyalty_points_used > 0 && $subscription->loyalty_deducted_at === null) {
                if (! $user) {
                    throw ValidationException::withMessages([
                        'loyalty_points' => ['Unable to redeem loyalty points for this subscription.'],
                    ]);
                }

                $deducted = Loyalty::applyRedemption($user, (int) $subscription->loyalty_points_used);
                if ($deducted !== (int) $subscription->loyalty_points_used) {
                    throw ValidationException::withMessages([
                        'loyalty_points' => ['Insufficient loyalty points to redeem.'],
                    ]);
                }

                $subscription->loyalty_deducted_at = now();
            } elseif (! $supportsManualColumns && $subscription->loyalty_points_used > 0 && $user) {
                Loyalty::applyRedemption($user, (int) $subscription->loyalty_points_used);
            }

            if ($supportsManualColumns && $subscription->loyalty_points_awarded <= 0 && $subscription->amount_paid_cents > 0) {
                $subscription->loyalty_points_awarded = Loyalty::earnForAmount($subscription->amount_paid_cents / 100);
            }

            if ($supportsManualColumns && $subscription->loyalty_points_awarded > 0 && $subscription->loyalty_awarded_at === null && $user) {
                Loyalty::award($user, (int) $subscription->loyalty_points_awarded);
                $subscription->loyalty_awarded_at = now();
            } elseif (! $supportsManualColumns && $subscription->loyalty_points_awarded > 0 && $user) {
                Loyalty::award($user, (int) $subscription->loyalty_points_awarded);
            }

            $subscription->status = 'approved';
            $subscription->coins_awarded = $plan->coins;
            $subscription->starts_at = now();
            $subscription->expires_at = $plan->valid_days ? now()->addDays($plan->valid_days) : null;
            if ($supportsManualColumns) {
                $subscription->approved_at = now();
            }
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

            $ledgerMeta = [
                'subscription_id' => $subscription->id,
                'plan_id' => $plan->id,
            ];
            if ($supportsManualColumns) {
                $ledgerMeta['payment_method'] = $subscription->payment_method;
            }

            WalletLedger::create([
                'fixer_id' => $subscription->fixer_id,
                'delta' => (int) $plan->coins,
                'reason' => 'purchase',
                'meta' => $ledgerMeta,
            ]);

            return [
                'user_id' => $user?->id,
                'first_name' => $user?->first_name,
                'last_name' => $user?->last_name,
                'plan_name' => $plan->name,
                'coins' => (int) $plan->coins,
                'payment_reference' => $subscription->payment_reference,
                'expires_at' => $subscription->expires_at,
            ];
        });

        if (! empty($context['user_id'])) {
            $this->notifyFixerSubscriptionApproved($context);
        }
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

    /**
     * Sends an in-app notification to the fixer once a subscription is approved.
     */
    protected function notifyFixerSubscriptionApproved(array $context): void
    {
        try {
            $fullName = trim(($context['first_name'] ?? '') . ' ' . ($context['last_name'] ?? ''));
            $title = 'Subscription approved';

            $plan = $context['plan_name'] ?? 'your plan';
            $coins = $context['coins'] ?? 0;
            $reference = $context['payment_reference'] ?? null;
            $expires = $context['expires_at'] ?? null;

            $parts = [
                "We credited {$coins} coin(s) from {$plan}.",
            ];

            if ($reference) {
                $parts[] = "Reference: {$reference}.";
            }

            if ($expires instanceof \DateTimeInterface) {
                $parts[] = 'Expires ' . $expires->format('d M Y');
            }

            $body = implode(' ', $parts);

            Notification::create([
                'recipient_type' => 'Individual',
                'user_id' => $context['user_id'],
                'title' => $title,
                'message' => $body,
                'read' => false,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to send subscription approval notification', [
                'user_id' => $context['user_id'] ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
