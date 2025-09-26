<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fixer;
use App\Models\ServiceRequest;
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
}

