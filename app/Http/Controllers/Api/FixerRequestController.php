<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fixer;
use App\Models\ServiceRequest;
use App\Models\Payment;
use App\Models\Notification;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FixerRequestController extends Controller
{
    /**
     * GET /api/fixer/requests
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        /** @var Fixer|null $fixer */
        $fixer = $user->fixer;
        if (! $fixer) {
            abort(403, 'Forbidden');
        }

        $status = $request->query('status');
        $q = ServiceRequest::with(['service', 'customer'])
            ->where('fixer_id', $fixer->id)
            ->latest();
        if ($status) {
            $q->where('status', $status);
        }
        return response()->json(['success' => true, 'data' => $q->paginate(20)]);
    }

    /**
     * POST /api/service-requests/{id}/accept
     * Assigns the request to the fixer (if unassigned) and deducts 1 coin atomically.
     */
    public function accept(ServiceRequest $serviceRequest, Request $request, WalletService $wallets): JsonResponse
    {
        $user = $request->user();
        /** @var Fixer|null $fixer */
        $fixer = $user->fixer;
        if (! $fixer) {
            abort(403, 'Forbidden');
        }

        // Only accept if unassigned or already assigned to this fixer
        if ($serviceRequest->fixer_id && $serviceRequest->fixer_id !== $fixer->id) {
            abort(403, 'Already assigned');
        }

        DB::transaction(function () use ($serviceRequest, $fixer, $wallets) {
            // Deduct 1 coin first to enforce business rules
            $wallets->deductOnAccept($fixer->id, $serviceRequest->id);

            if (! $serviceRequest->fixer_id) {
                $serviceRequest->fixer_id = $fixer->id;
            }
            $serviceRequest->status = 'accepted';
            $serviceRequest->save();
        });

        return response()->json([
            'success' => true,
            'data' => $serviceRequest->fresh()->load(['service', 'fixer.user']),
            'message' => '1 coin deducted. Request accepted.',
        ]);
    }

    /**
     * POST /api/fixer/requests/{id}/bill
     * Allows an assigned fixer to create/update a bill for a service request.
     */
    public function bill(ServiceRequest $serviceRequest, Request $request): JsonResponse
    {
        $user = $request->user();
        /** @var Fixer|null $fixer */
        $fixer = $user->fixer;
        if (! $fixer) abort(403, 'Forbidden');

        // Must be assigned to this fixer
        if ($serviceRequest->fixer_id !== $fixer->id) abort(403, 'Forbidden');

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $payment = $serviceRequest->payment;
        if ($payment) {
            if ($payment->status === 'paid') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment has already been completed for this request.',
                ], 422);
            }

            $payment->update([
                'amount' => $validated['amount'],
                'status' => 'pending',
            ]);
        } else {
            $payment = Payment::create([
                'service_request_id' => $serviceRequest->id,
                'amount' => $validated['amount'],
                'status' => 'pending',
            ]);
        }

        // Set awaiting_payment so both apps can reflect the state clearly
        $serviceRequest->status = 'awaiting_payment';
        $serviceRequest->save();

        // Optionally: set request state to accepted (or leave) and notify customer here
        // $serviceRequest->status = 'accepted';
        // $serviceRequest->save();

        // Create an in-app notification for the customer
        try {
            Notification::create([
                'user_id' => $serviceRequest->customer_id,
                'title' => 'Payment Required',
                'message' => 'A bill of ' . number_format((float) $validated['amount'], 2) . ' has been issued for your ' . optional($serviceRequest->service)->name . ' request.',
                'read' => false,
            ]);
        } catch (\Throwable $e) {
            // ignore if notification model/schema differs
        }

        return response()->json([
            'success' => true,
            'data' => $payment->fresh(),
            'message' => 'Bill created and sent to customer.',
        ]);
    }
}
