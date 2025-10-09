<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fixer;
use App\Models\FixerSubscription;
use App\Models\Notification;
use App\Models\PaymentMethod;
use App\Models\SubscriptionPlan;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Support\Loyalty;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        $methodCode = strtolower($validated['payment_method']);

        $paymentMethod = PaymentMethod::query()
            ->whereRaw('LOWER(code) = ?', [$methodCode])
            ->where('active', true)
            ->first();

        if (! $paymentMethod) {
            throw ValidationException::withMessages([
                'payment_method' => ['Unsupported payment method selected.'],
            ]);
        }

        $supportsManualColumns = Schema::hasColumn('fixer_subscriptions', 'payment_method');

        return DB::transaction(function () use (
            $plan,
            $user,
            $fixer,
            $methodCode,
            $paymentMethod,
            $validated,
            $walletService,
            $supportsManualColumns
        ) {
            $priceKwacha = $plan->price_cents / 100;
            $requestedPoints = max(0, (int) ($validated['loyalty_points'] ?? 0));
            $availablePoints = (int) $user->loyalty_points;
            $maxRedeemablePoints = Loyalty::pointsForValue($priceKwacha);
            $pointsToUse = min($requestedPoints, $availablePoints, $maxRedeemablePoints);

            $pointValue = Loyalty::pointValue();
            $loyaltyValue = $pointsToUse * $pointValue;
            $amountDueKwacha = max(0, $priceKwacha - $loyaltyValue);
            $amountDueCents = (int) round($amountDueKwacha * 100);

            $paymentReference = strtoupper(Str::random(12));

            $manualConfig = config("payments.$methodCode", []);
            $isManual = ! empty($manualConfig);
            $manualPhone = $manualConfig['phone'] ?? null;
            $manualAccount = $manualConfig['account'] ?? null;
            $instructionsTemplate = $manualConfig['instructions'] ?? $paymentMethod?->integration_note;
            $methodTitle = $manualConfig['title']
                ?? $paymentMethod?->name
                ?? ucfirst(str_replace('_', ' ', $methodCode));
            $formattedInstructions = $this->formatInstructions($instructionsTemplate, [
                ':amount' => 'K' . number_format($amountDueKwacha, 2, '.', ''),
                ':reference' => $paymentReference,
                ':plan' => $plan->name,
                ':coins' => (string) $plan->coins,
                ':method' => $methodTitle,
                ':phone' => $manualPhone ?? '',
                ':account' => $manualAccount ?? $methodTitle,
            ]);

            $attributes = [
                'fixer_id' => $fixer->id,
                'subscription_plan_id' => $plan->id,
                'payment_reference' => $paymentReference,
                'status' => 'pending',
                'coins_awarded' => 0,
                'amount_paid_cents' => $amountDueCents,
                'loyalty_points_used' => $pointsToUse,
                'loyalty_points_awarded' => 0,
            ];

            if ($supportsManualColumns) {
                $attributes['payment_method'] = $methodCode;
                $attributes['payment_instructions'] = $isManual ? $formattedInstructions : null;
                $attributes['payment_meta'] = [
                    'amount_due_cents' => $amountDueCents,
                    'loyalty_discount_cents' => (int) round($loyaltyValue * 100),
                    'requested_loyalty_points' => $pointsToUse,
                    'point_value' => $pointValue,
                ];
            }

            $subscription = FixerSubscription::create($attributes);

            if ($isManual) {
                $subscription->load('plan');

                $this->notifyManualPaymentPending($subscription, $fixer, $plan, $amountDueKwacha, $paymentReference, $methodTitle);

                return response()->json([
                    'success' => true,
                    'data' => [
                        'subscription' => $subscription,
                        'requires_manual_payment' => true,
                        'payment_method' => $methodCode,
                        'payment_title' => $methodTitle,
                        'payment_reference' => $paymentReference,
                        'payment_instructions' => $formattedInstructions,
                        'payment_phone' => $manualPhone,
                        'payment_account' => $manualAccount,
                        'amount_due' => $amountDueKwacha,
                        'loyalty_points_used' => $pointsToUse,
                        'loyalty_points_balance' => $user->loyalty_points,
                        'loyalty_points_earned_when_approved' => Loyalty::earnForAmount($amountDueKwacha),
                    ],
                    'message' => 'Payment request received. Follow the instructions to complete payment. We will notify you once approved.',
                ], 201);
            }

            $walletService->approveSubscriptionAndCredit($subscription);

            $subscription->refresh()->load('plan');
            $fixer->refresh();
            $user->refresh();

            $wallet = $fixer->wallet()->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'subscription' => $subscription,
                    'wallet' => $wallet,
                    'loyalty_points_balance' => $user->loyalty_points,
                    'loyalty_points_earned' => $subscription->loyalty_points_awarded ?? 0,
                    'requires_manual_payment' => false,
                ],
                'message' => 'Subscription purchased successfully.',
            ], 201);
        });
    }

    protected function formatInstructions(?string $template, array $replacements): ?string
    {
        if ($template === null) {
            return null;
        }

        return strtr($template, $replacements);
    }

    protected function notifyManualPaymentPending(
        FixerSubscription $subscription,
        Fixer $fixer,
        SubscriptionPlan $plan,
        float $amountDueKwacha,
        string $paymentReference,
        string $methodTitle
    ): void {
        $user = $fixer->user;
        $fixerName = trim(($user?->first_name ?? '') . ' ' . ($user?->last_name ?? '')) ?: $user?->name ?? 'A fixer';
        $planName = $plan->name;
        $amountFormatted = 'K' . number_format($amountDueKwacha, 2, '.', '');

        $message = sprintf(
            '%s submitted a %s payment for %s (%s). Reference: %s. Review and approve once funds arrive.',
            $fixerName,
            $methodTitle,
            $planName,
            $amountFormatted,
            $paymentReference
        );

        foreach (['Admin', 'Support'] as $audience) {
            try {
                Notification::create([
                    'recipient_type' => $audience,
                    'user_id' => null,
                    'title' => 'Manual subscription payment pending',
                    'message' => $message,
                    'read' => false,
                ]);
            } catch (\Throwable $e) {
                Log::warning('Unable to create manual payment notification', [
                    'subscription_id' => $subscription->id,
                    'audience' => $audience,
                    'error' => $e->getMessage(),
                ]);
            }
        }
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
