<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Payment;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\DB;
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

            $payment = $serviceRequest->payment;
            $previousCouponId = $payment?->coupon_id;

            $originalAmount = $validated['original_amount'] ?? ($payment->original_amount ?? $validated['amount']);
            if ($originalAmount < $validated['amount']) {
                $originalAmount = $validated['amount'];
            }

            $coupon = null;
            $discount = 0.0;
            $finalAmount = $validated['amount'];

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

            $attributes = [
                'amount' => $finalAmount,
                'original_amount' => $originalAmount,
                'discount_amount' => $discount,
                'status' => $isPaid ? 'paid' : $status,
                'coupon_id' => $coupon?->id,
            ];

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

            if ($isPaid) {
                $serviceRequest->status = 'completed';
                $serviceRequest->save();
            }

            return response()->json(['success' => true, 'data' => $payment->fresh(['coupon'])]);
        });
    }
}
