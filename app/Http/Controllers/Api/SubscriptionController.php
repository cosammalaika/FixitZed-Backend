<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fixer;
use App\Models\FixerSubscription;
use App\Models\SubscriptionPlan;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Support\Loyalty;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SubscriptionController extends Controller
{
    public function plans(): JsonResponse
    {
        $plans = SubscriptionPlan::where('is_active', true)->orderBy('price_cents')->get();
        return response()->json(['success' => true, 'data' => $plans]);
    }

    /**
     * Creates a pending subscription record and returns a mock payment reference.
     * Integrate with your PSP by creating a proper payment intent here.
     */
    public function checkout(Request $request, WalletService $walletService): JsonResponse
    {
        $validated = $request->validate([
            'plan_id' => ['required', 'exists:subscription_plans,id'],
            'payment_method' => ['required', 'string', 'max:100'],
            'loyalty_points' => ['nullable', 'integer', 'min:0'],
        ]);

        $user = $request->user();
        /** @var Fixer|null $fixer */
        $fixer = $user->fixer;
        if (! $fixer) {
            throw ValidationException::withMessages([
                'fixer' => ['Only fixers can purchase plans.'],
            ]);
        }

        $plan = SubscriptionPlan::findOrFail($validated['plan_id']);

        return DB::transaction(function () use ($plan, $user, $fixer, $validated, $walletService) {
            $priceKwacha = $plan->price_cents / 100;
            $requestedPoints = (int) ($validated['loyalty_points'] ?? 0);
            $available = (int) $user->loyalty_points;
            $maxNeeded = Loyalty::pointsForValue($priceKwacha);
            $pointsToUse = min($requestedPoints, $available, $maxNeeded);

            $loyaltyValue = 0.0;
            if ($pointsToUse > 0) {
                $pointsToUse = Loyalty::applyRedemption($user, $pointsToUse);
                $loyaltyValue = Loyalty::maxRedeemableValue($pointsToUse);
            }

            $amountDueKwacha = max(0, $priceKwacha - $loyaltyValue);
            $amountDueCents = (int) round($amountDueKwacha * 100);

            $subscription = FixerSubscription::create([
                'fixer_id' => $fixer->id,
                'subscription_plan_id' => $plan->id,
                'payment_reference' => Str::uuid()->toString(),
                'status' => 'pending',
                'coins_awarded' => 0,
                'amount_paid_cents' => $amountDueCents,
                'loyalty_points_used' => $pointsToUse,
            ]);

            $walletService->approveSubscriptionAndCredit($subscription);

            $subscription->refresh();

            $pointsEarned = Loyalty::earnForAmount($amountDueKwacha);
            if ($pointsEarned > 0) {
                Loyalty::award($user, $pointsEarned);
            }

            if (Schema::hasColumn('fixer_subscriptions', 'loyalty_points_awarded')) {
                $subscription->loyalty_points_awarded = $pointsEarned;
                $subscription->save();
            }

            $wallet = $fixer->wallet()->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'subscription' => $subscription->load('plan'),
                    'wallet' => $wallet,
                    'loyalty_points_balance' => $user->loyalty_points,
                    'loyalty_points_earned' => $pointsEarned,
                ],
                'message' => 'Subscription purchased successfully.',
            ], 201);
        });
    }

    /**
     * Webhook to approve a payment and credit the wallet.
     * Accepts either subscription_id or payment_reference.
     */
    public function webhook(Request $request, WalletService $walletService): JsonResponse
    {
        $validated = $request->validate([
            'subscription_id' => ['nullable', 'integer', 'exists:fixer_subscriptions,id'],
            'payment_reference' => ['nullable', 'string'],
            'status' => ['required', 'in:success,failed'],
        ]);

        /** @var FixerSubscription|null $subscription */
        $subscription = null;
        if (!empty($validated['subscription_id'])) {
            $subscription = FixerSubscription::with('plan')->find($validated['subscription_id']);
        } elseif (!empty($validated['payment_reference'])) {
            $subscription = FixerSubscription::with('plan')->where('payment_reference', $validated['payment_reference'])->first();
        }
        if (! $subscription) {
            return response()->json(['success' => false, 'message' => 'Subscription not found'], 404);
        }

        if ($validated['status'] === 'failed') {
            $subscription->status = 'failed';
            $subscription->save();
            return response()->json(['success' => true]);
        }

        // success path
        $walletService->approveSubscriptionAndCredit($subscription);
        return response()->json(['success' => true]);
    }

    public function myWallet(Request $request): JsonResponse
    {
        $user = $request->user();
        /** @var Fixer|null $fixer */
        $fixer = $user->fixer;
        if (! $fixer) {
            throw ValidationException::withMessages([
                'fixer' => ['Only fixers can view wallet.'],
            ]);
        }
        $fixer->load('wallet');
        $wallet = $fixer->wallet;

        $totalEarnings = $fixer->earnings()->sum('amount');

        $payload = $wallet ? $wallet->toArray() : [];
        $payload['total_earnings'] = (float) $totalEarnings;
        $payload['total_earnings_formatted'] = number_format($totalEarnings, 2, '.', '');

        return response()->json([
            'success' => true,
            'data' => $payload,
        ]);
    }
}
