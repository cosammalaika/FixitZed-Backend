<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Services\FixerAssignmentService;
use App\Services\ServiceRequestCancellationService;
use App\Support\CancellationReasonOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ServiceRequestController extends Controller
{
    public function __construct(
        private FixerAssignmentService $assignment,
        private ServiceRequestCancellationService $cancellations
    )
    {
    }
    public function index(Request $request)
    {
        $requests = ServiceRequest::with(['service', 'fixer.user'])
            ->where('customer_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $requests,
            'meta' => ['count' => $requests->count()],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'scheduled_at' => ['required', 'date'],
            'location' => ['required', 'string', 'max:255'],
            'location_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'location_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'customer_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $sr = ServiceRequest::create([
            'customer_id' => $request->user()->id,
            'service_id' => $validated['service_id'],
            'scheduled_at' => $validated['scheduled_at'],
            'location' => $validated['location'],
            'location_lat' => $validated['location_lat'] ?? null,
            'location_lng' => $validated['location_lng'] ?? null,
            'customer_note' => $validated['customer_note'] ?? null,
            'status' => 'pending',
        ]);

        Log::info('[FIXITZED_TRACE] booking.created', [
            'request_id' => $sr->id,
            'service_id' => $sr->service_id,
            'customer_id' => $request->user()->id,
            'status' => $sr->status,
            'fixer_id_before_assignment' => $sr->fixer_id,
            'scheduled_at' => $sr->scheduled_at,
        ]);

        $this->assignment->assign($sr);

        Log::info('[FIXITZED_TRACE] booking.assigned', [
            'request_id' => $sr->id,
            'service_id' => $sr->service_id,
            'customer_id' => $request->user()->id,
            'status' => $sr->status,
            'fixer_id_after_assignment' => $sr->fixer_id,
            'scheduled_at' => $sr->scheduled_at,
        ]);

        return response()->json([
            'success' => true,
            'data' => $sr->load(['service', 'fixer.user']),
            'meta' => ['count' => 1],
        ], 201);
    }

    public function show(ServiceRequest $serviceRequest, Request $request)
    {
        $this->authorizeView($serviceRequest, $request);
        return response()->json([
            'success' => true,
            'data' => $serviceRequest->load(['service', 'fixer.user']),
            'meta' => ['count' => 1],
        ]);
    }

    public function update(ServiceRequest $serviceRequest, Request $request)
    {
        $this->authorizeView($serviceRequest, $request);
        $validated = $request->validate([
            'status' => [
                'sometimes',
                Rule::in(['pending', 'accepted', 'completed', 'cancelled', 'awaiting_payment', 'expired'])
            ],
            'scheduled_at' => ['sometimes', 'date'],
            'location' => ['sometimes', 'string', 'max:255'],
            'customer_note' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ]);

        $serviceRequest->update($validated);

        return response()->json([
            'success' => true,
            'data' => $serviceRequest->fresh()->load(['service', 'fixer.user']),
        ]);
    }

    public function cancel(ServiceRequest $serviceRequest, Request $request)
    {
        $this->authorizeView($serviceRequest, $request);

        $validated = $request->validate([
            'reason_key' => ['required', 'string', Rule::in(CancellationReasonOptions::keys())],
            'note' => [
                'nullable',
                'string',
                'max:1000',
                Rule::requiredIf(fn () => $request->input('reason_key') === CancellationReasonOptions::OTHER),
            ],
        ]);

        $serviceRequest = $this->cancellations->cancelByCustomer(
            $serviceRequest,
            $request->user(),
            (string) $validated['reason_key'],
            $validated['note'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled.',
            'data' => $serviceRequest,
            'meta' => ['count' => 1],
        ]);
    }

    protected function authorizeView(ServiceRequest $serviceRequest, Request $request): void
    {
        abort_if($serviceRequest->customer_id !== $request->user()->id, 403, 'Forbidden');
    }
}
