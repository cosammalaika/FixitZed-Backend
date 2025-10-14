<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Services\FixerAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceRequestController extends Controller
{
    public function __construct(private FixerAssignmentService $assignment)
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
        ]);

        $sr = ServiceRequest::create([
            'customer_id' => $request->user()->id,
            'service_id' => $validated['service_id'],
            'scheduled_at' => $validated['scheduled_at'],
            'location' => $validated['location'],
            'location_lat' => $validated['location_lat'] ?? null,
            'location_lng' => $validated['location_lng'] ?? null,
            'status' => 'pending',
        ]);

        $this->assignment->assign($sr);

        return response()->json([
            'success' => true,
            'data' => $sr->load(['service', 'fixer.user']),
        ], 201);
    }

    public function show(ServiceRequest $serviceRequest, Request $request)
    {
        $this->authorizeView($serviceRequest, $request);
        return response()->json([
            'success' => true,
            'data' => $serviceRequest->load(['service', 'fixer.user']),
        ]);
    }

    public function update(ServiceRequest $serviceRequest, Request $request)
    {
        $this->authorizeView($serviceRequest, $request);
        $validated = $request->validate([
            'status' => [
                'sometimes',
                Rule::in(['pending', 'accepted', 'completed', 'cancelled', 'awaiting_payment'])
            ],
            'scheduled_at' => ['sometimes', 'date'],
            'location' => ['sometimes', 'string', 'max:255'],
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

        if ($serviceRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending bookings can be cancelled.',
            ], 422);
        }

        if ($serviceRequest->fixer_id) {
            return response()->json([
                'success' => false,
                'message' => 'A fixer has already been assigned. Please contact support for assistance.',
            ], 422);
        }

        $serviceRequest->forceFill([
            'status' => 'cancelled',
            'fixer_snoozed_until' => null,
        ])->save();

        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled.',
            'data' => $serviceRequest->fresh()->load(['service', 'fixer.user']),
        ]);
    }

    protected function authorizeView(ServiceRequest $serviceRequest, Request $request): void
    {
        abort_if($serviceRequest->customer_id !== $request->user()->id, 403, 'Forbidden');
    }
}
