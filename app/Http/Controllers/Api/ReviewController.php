<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\ResolvesPerPage;
use App\Models\Review;
use App\Models\Service;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    use ResolvesPerPage;

    public function index(Service $service)
    {
        $perPage = $this->resolvePerPage(request());
        $reviews = Review::with('user')
            ->where('service_id', $service->id)
            ->latest()
            ->paginate($perPage);

        return response()->json(['success' => true, 'data' => $reviews]);
    }

    public function store(Service $service, Request $request)
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $review = Review::create([
            'user_id' => $request->user()->id,
            'service_id' => $service->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return response()->json(['success' => true, 'data' => $review->load('user')], 201);
    }
}
