<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fixer;
use App\Models\User;
use Illuminate\Http\Request;

class FixerController extends Controller
{
    public function index(Request $request)
    {
        $q = Fixer::query()->with(['user', 'services', 'wallet']);

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
        $fixer->load(['user', 'services', 'wallet']);
        return response()->json([
            'success' => true,
            'data' => $fixer,
        ]);
    }

    public function top(Request $request)
    {
        $limit = (int) $request->query('limit', 10);

        $users = User::where('user_type', 'Fixer')
            ->withAvg([
                'receivedRatings as average_rating' => function ($query) {
                    $query->where('role', 'customer');
                }
            ], 'rating')
            ->orderByDesc('average_rating')
            ->take($limit)
            ->get();

        $data = $users->map(function (User $u) {
            $name = trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? ''));
            return [
                'id' => $u->id,
                'name' => $name !== '' ? $name : ($u->username ?? 'Fixer'),
                'full_name' => $name,
                'username' => $u->username,
                'avatar' => $u->avatar_url,
                'image_url' => $u->avatar_url,
                'photo' => $u->avatar_url,
                'average_rating' => $u->average_rating ? round((float) $u->average_rating, 1) : null,
                'avg_rating' => $u->average_rating ? round((float) $u->average_rating, 1) : null,
                'rating' => $u->average_rating ? round((float) $u->average_rating, 1) : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
