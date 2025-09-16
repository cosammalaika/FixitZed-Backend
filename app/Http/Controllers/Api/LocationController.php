<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $locations = Location::where('user_id', $request->user()->id)->latest()->get();
        return response()->json(['success' => true, 'data' => $locations]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'address' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        $loc = Location::create($validated + ['user_id' => $request->user()->id]);
        return response()->json(['success' => true, 'data' => $loc], 201);
    }

    public function update(Location $location, Request $request)
    {
        abort_if($location->user_id !== $request->user()->id, 403, 'Forbidden');
        $validated = $request->validate([
            'address' => ['sometimes', 'string', 'max:255'],
            'latitude' => ['sometimes', 'numeric'],
            'longitude' => ['sometimes', 'numeric'],
        ]);
        $location->update($validated);
        return response()->json(['success' => true, 'data' => $location->fresh()]);
    }

    public function destroy(Location $location, Request $request)
    {
        abort_if($location->user_id !== $request->user()->id, 403, 'Forbidden');
        $location->delete();
        return response()->json(['success' => true]);
    }
}

