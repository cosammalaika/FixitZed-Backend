<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function show(ServiceRequest $serviceRequest, Request $request)
    {
        abort_if($serviceRequest->customer_id !== $request->user()->id, 403, 'Forbidden');
        $payment = $serviceRequest->payment;
        return response()->json(['success' => true, 'data' => $payment]);
    }

    public function store(ServiceRequest $serviceRequest, Request $request)
    {
        abort_if($serviceRequest->customer_id !== $request->user()->id, 403, 'Forbidden');

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string'],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'transaction_id' => ['nullable', 'string', 'max:191'],
        ]);

        $payment = $serviceRequest->payment;
        if ($payment) {
            $payment->update($validated + ['paid_at' => $validated['status'] === 'paid' ? now() : $payment->paid_at]);
        } else {
            $payment = Payment::create($validated + [
                'service_request_id' => $serviceRequest->id,
                'paid_at' => $validated['status'] === 'paid' ? now() : null,
            ]);
        }

        return response()->json(['success' => true, 'data' => $payment->fresh()]);
    }
}

