<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RatingController extends Controller
{
    public function store(ServiceRequest $serviceRequest, Request $request)
    {
        // Only allow the customer who created the service request to rate the fixer
        abort_if($serviceRequest->customer_id !== $request->user()->id, 403, 'Forbidden');

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $ratedUserId = optional($serviceRequest->fixer?->user)->id;
        abort_if(!$ratedUserId, 422, 'Service request has no assigned fixer');

        $rating = Rating::create([
            'rater_id' => $request->user()->id,
            'rated_user_id' => $ratedUserId,
            'service_request_id' => $serviceRequest->id,
            'role' => 'customer',
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return response()->json(['success' => true, 'data' => $rating]);
    }

    public function listForUser(User $user)
    {
        $ratings = Rating::with(['rater', 'serviceRequest'])
            ->where('rated_user_id', $user->id)
            ->latest()
            ->paginate(20);
        return response()->json(['success' => true, 'data' => $ratings]);
    }
}

