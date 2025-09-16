<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceRequestController extends Controller
{
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
            'fixer_id' => ['nullable', 'integer', 'exists:fixers,id'],
        ]);

        $sr = ServiceRequest::create([
            'customer_id' => $request->user()->id,
            'service_id' => $validated['service_id'],
            'scheduled_at' => $validated['scheduled_at'],
            'location' => $validated['location'],
            'fixer_id' => $validated['fixer_id'] ?? null,
            'status' => 'pending',
        ]);

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
                Rule::in(['pending', 'accepted', 'completed', 'cancelled'])
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

    protected function authorizeView(ServiceRequest $serviceRequest, Request $request): void
    {
        abort_if($serviceRequest->customer_id !== $request->user()->id, 403, 'Forbidden');
    }
}

