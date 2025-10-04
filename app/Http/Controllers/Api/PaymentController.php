<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Earning;
use App\Models\Payment;
use App\Models\ServiceRequest;
use App\Models\Setting;
use App\Support\Loyalty;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function show(ServiceRequest $serviceRequest, Request $request)
    {
        abort_if($serviceRequest->customer_id !== $request->user()->id, 403, 'Forbidden');
        $payment = $serviceRequest->payment?->load('coupon');
        if ($payment) {
            $payment->setRelation('service_request', $serviceRequest->load('service'));
        }

        return response()->json([
            'success' => true,
            'data' => $payment,
        ]);
    }

    public function store(ServiceRequest $serviceRequest, Request $request)
    {
        abort_if($serviceRequest->customer_id !== $request->user()->id, 403, 'Forbidden');

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string'],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'transaction_id' => ['nullable', 'string', 'max:191'],
            'original_amount' => ['nullable', 'numeric', 'min:0'],
            'coupon_code' => ['nullable', 'string', 'max:100'],
        ]);

        return DB::transaction(function () use ($serviceRequest, $request, $validated) {
            $status = strtolower($validated['status']);
            $isPaid = in_array($status, ['paid', 'completed'], true);

            /** @var \App\Models\User $user */
            $user = $request->user();
            $payment = $serviceRequest->payment;
            $wasPaid = $payment && in_array(strtolower($payment->status), ['paid', 'completed'], true);
            $previousCouponId = $payment?->coupon_id;

            $originalAmount = $validated['original_amount'] ?? ($payment->original_amount ?? $validated['amount']);
            if ($originalAmount < $validated['amount']) {
                $originalAmount = $validated['amount'];
            }

            $coupon = null;
            $discount = 0.0;
            $finalAmount = $validated['amount'];
            $loyaltyValue = 0.0;
            $loyaltyPointsUsed = 0;
            $loyaltyPointsEarned = 0;

            $couponCode = trim((string) ($validated['coupon_code'] ?? ''));
            if ($couponCode !== '') {
                $coupon = Coupon::where('code', $couponCode)->lockForUpdate()->first();
                if (! $coupon || ! $coupon->isValid()) {
                    abort(422, 'Invalid or expired coupon.');
                }

                $percent = (float) ($coupon->discount_percent ?? 0);
                $flat = (float) ($coupon->discount_amount ?? 0);
                $discount = round($originalAmount * ($percent / 100), 2) + $flat;
                if ($discount > $originalAmount) {
                    $discount = $originalAmount;
                }
                $finalAmount = round($originalAmount - $discount, 2);
            } else {
                // If the client passed a smaller amount than original, treat difference as discount
                $discount = max(0, round($originalAmount - $finalAmount, 2));
            }

            $requestedPoints = (int) ($request->input('loyalty_points') ?? 0);
            if ($requestedPoints > 0 && Schema::hasColumn('payments', 'loyalty_points_used')) {
                $available = (int) $user->loyalty_points;
                $maxNeeded = Loyalty::pointsForValue($finalAmount);
                $pointsToUse = min($requestedPoints, $available, $maxNeeded);
                if ($pointsToUse > 0) {
                    $loyaltyPointsUsed = Loyalty::applyRedemption($user, $pointsToUse);
                    if ($loyaltyPointsUsed > 0) {
                        $loyaltyValue = Loyalty::maxRedeemableValue($loyaltyPointsUsed);
                        $finalAmount = max(0, round($finalAmount - $loyaltyValue, 2));
                        $discount += $loyaltyValue;
                    }
                }
            }

            $loyaltyPointsEarned = Loyalty::earnForAmount($finalAmount);

            $attributes = [
                'amount' => $finalAmount,
                'status' => $isPaid ? 'paid' : $status,
            ];

            if (Schema::hasColumn('payments', 'original_amount')) {
                $attributes['original_amount'] = $originalAmount;
            }

            if (Schema::hasColumn('payments', 'discount_amount')) {
                $attributes['discount_amount'] = $discount;
            }

            if (Schema::hasColumn('payments', 'coupon_id')) {
                $attributes['coupon_id'] = $coupon?->id;
            }

            if (Schema::hasColumn('payments', 'loyalty_points_used')) {
                $attributes['loyalty_points_used'] = $loyaltyPointsUsed;
            }

            if (array_key_exists('payment_method', $validated)) {
                $attributes['payment_method'] = $validated['payment_method'];
            }

            if (array_key_exists('transaction_id', $validated)) {
                $attributes['transaction_id'] = $validated['transaction_id'];
            }

            if ($isPaid) {
                $attributes['paid_at'] = now();
            }

            if ($payment) {
                if (! $isPaid && $payment->paid_at && ! array_key_exists('paid_at', $attributes)) {
                    $attributes['paid_at'] = $payment->paid_at;
                }
                $payment->update($attributes);
            } else {
                $payment = Payment::create($attributes + [
                    'service_request_id' => $serviceRequest->id,
                    'paid_at' => $attributes['paid_at'] ?? null,
                ]);
            }

            // Adjust coupon usage counts when the applied coupon changes
            if ($previousCouponId && (! $coupon || $coupon->id !== $previousCouponId)) {
                Coupon::where('id', $previousCouponId)->where('used_count', '>', 0)->decrement('used_count');
            }
            if ($coupon && $coupon->id !== $previousCouponId) {
                $coupon->increment('used_count');
            }

            if ($loyaltyPointsEarned > 0) {
                Loyalty::award($user, $loyaltyPointsEarned);
            }

            if ($isPaid && ! $wasPaid) {
                $serviceRequest->status = 'completed';
                $serviceRequest->save();

                $fixer = $serviceRequest->fixer;
                if ($fixer) {
                    $earning = Earning::firstOrNew(['fixer_id' => $fixer->id]);
                    $earning->amount = ($earning->amount ?? 0) + $finalAmount;
                    $earning->service_count = ($earning->service_count ?? 0) + 1;
                    $earning->save();

                    $points = (int) Setting::get('loyalty.fixer_completion_points', 10);
                    if ($points > 0 && $fixer->user) {
                        Loyalty::award($fixer->user, $points);
                    }
                }
            }

            $freshPayment = $payment->fresh(['coupon']);

            $responseData = $freshPayment ? $freshPayment->toArray() : [];
            $responseData['loyalty_points_balance'] = $user->loyalty_points;
            $responseData['loyalty_points_earned'] = $loyaltyPointsEarned;

            return response()->json(['success' => true, 'data' => $responseData]);
        });
    }
}
