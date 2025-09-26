<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fixer;
use App\Models\FixerSubscription;
use App\Models\SubscriptionPlan;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
    public function checkout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plan_id' => ['required', 'exists:subscription_plans,id'],
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

        $subscription = FixerSubscription::create([
            'fixer_id' => $fixer->id,
            'subscription_plan_id' => $plan->id,
            'payment_reference' => Str::uuid()->toString(),
            'status' => 'pending',
            'coins_awarded' => $plan->coins,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'subscription_id' => $subscription->id,
                'payment_reference' => $subscription->payment_reference,
                'amount_cents' => $plan->price_cents,
                'currency' => 'ZMW',
                'plan' => $plan,
            ],
        ], 201);
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
        return response()->json([
            'success' => true,
            'data' => $fixer->wallet,
        ]);
    }
}

