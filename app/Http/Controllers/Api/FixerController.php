<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fixer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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
            ->with(['fixer.services'])
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
            $fixer = $u->fixer;
            $avg = $u->average_rating ? round((float) $u->average_rating, 1) : null;

            $services = $fixer?->services
                ? $fixer->services->map(function ($service) {
                    return [
                        'id' => $service->id,
                        'name' => $service->name,
                        'price' => $service->price,
                    ];
                })->values()
                : collect();

            return [
                'id' => $fixer?->id ?? $u->id,
                'user_id' => $u->id,
                'name' => $name !== '' ? $name : ($u->username ?? 'Fixer'),
                'full_name' => $name,
                'username' => $u->username,
                'avatar' => $u->avatar_url,
                'image_url' => $u->avatar_url,
                'photo' => $u->avatar_url,
                'bio' => $fixer?->bio,
                'status' => $fixer?->status,
                'average_rating' => $avg,
                'avg_rating' => $avg,
                'rating' => $avg,
                'services' => $services,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function apply(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'bio' => ['required', 'string', 'max:2000'],
            'service_ids' => ['required', 'array', 'min:1'],
            'service_ids.*' => ['integer', 'exists:services,id'],
        ]);

        return DB::transaction(function () use ($user, $validated) {
            $fixer = $user->fixer;

            if ($fixer && $fixer->status === 'approved') {
                abort(422, 'You are already an approved fixer.');
            }

            if (! $fixer) {
                $fixer = Fixer::create([
                    'user_id' => $user->id,
                    'bio' => $validated['bio'],
                    'status' => 'pending',
                ]);
            } else {
                $fixer->update([
                    'bio' => $validated['bio'],
                    'status' => 'pending',
                ]);
            }

            $fixer->services()->sync($validated['service_ids']);

            if ($user->user_type !== 'Fixer') {
                $user->user_type = 'Fixer';
                $user->save();
            }

            return response()->json([
                'success' => true,
                'data' => $fixer->load(['services', 'user']),
                'message' => 'Application submitted. We will review it shortly.',
            ]);
        });
    }
}
