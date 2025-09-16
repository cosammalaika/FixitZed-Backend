<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LocationOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class LocationOptionController extends Controller
{
    // Public: list active options for dropdown
    public function index()
    {
        if (!Schema::hasTable('location_options')) {
            return response()->json(['success' => true, 'data' => []]);
        }
        $options = LocationOption::where('is_active', true)->orderBy('name')->get();
        return response()->json(['success' => true, 'data' => $options]);
    }

    // Manage: create new location option
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $opt = LocationOption::create($data);
        return response()->json(['success' => true, 'data' => $opt], 201);
    }

    public function update(LocationOption $locationOption, Request $request)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:191'],
            'latitude' => ['sometimes', 'nullable', 'numeric'],
            'longitude' => ['sometimes', 'nullable', 'numeric'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $locationOption->update($data);
        return response()->json(['success' => true, 'data' => $locationOption->fresh()]);
    }

    public function toggle(LocationOption $locationOption)
    {
        $locationOption->update(['is_active' => !$locationOption->is_active]);
        return response()->json(['success' => true, 'data' => $locationOption->fresh()]);
    }

    public function destroy(LocationOption $locationOption)
    {
        $locationOption->delete();
        return response()->json(['success' => true]);
    }
}
