<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fixer;
use Illuminate\Http\Request;

class FixerController extends Controller
{
    public function index(Request $request)
    {
        $q = Fixer::query()->with(['user', 'services']);

        if ($request->filled('service_id')) {
            $q->whereHas('services', function ($qq) use ($request) {
                $qq->where('services.id', $request->integer('service_id'));
            });
        }

        if ($request->filled('status')) {
            $q->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $term = '%' . $request->input('search') . '%';
            $q->whereHas('user', function ($u) use ($term) {
                $u->where('first_name', 'like', $term)
                  ->orWhere('last_name', 'like', $term)
                  ->orWhere('email', 'like', $term);
            });
        }

        $fixers = $q->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $fixers,
        ]);
    }

    public function show(Fixer $fixer)
    {
        $fixer->load(['user', 'services']);
        return response()->json([
            'success' => true,
            'data' => $fixer,
        ]);
    }
}

