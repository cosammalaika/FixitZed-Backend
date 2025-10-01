<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fixer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
            ->withCount([
                'receivedRatings as ratings_count' => function ($query) {
                    $query->where('role', 'customer');
                }
            ])
            ->orderByDesc('average_rating')
            ->take($limit)
            ->get();

        $data = $users->map(function (User $u) {
            $name = trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? ''));
            $fixer = $u->fixer;
            $avg = $u->average_rating ? round((float) $u->average_rating, 1) : null;
            $ratingsCount = (int) ($u->ratings_count ?? 0);

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
                'ratings_count' => $ratingsCount,
                'services' => $services,
                'user' => [
                    'id' => $u->id,
                    'first_name' => $u->first_name,
                    'last_name' => $u->last_name,
                    'name' => $name !== '' ? $name : ($u->username ?? 'Fixer'),
                    'username' => $u->username,
                    'email' => $u->email,
                    'contact_number' => $u->contact_number,
                    'avatar_url' => $u->avatar_url,
                    'average_rating' => $avg,
                    'ratings_count' => $ratingsCount,
                ],
                'fixer_profile' => $fixer ? [
                    'id' => $fixer->id,
                    'bio' => $fixer->bio,
                    'status' => $fixer->status,
                    'services' => $services,
                ] : null,
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

        $user = $request->user();

        $validated = $request->validate([
            'bio' => ['required', 'string', 'max:2000'],
            'service_ids' => ['required', 'array', 'min:1'],
            'service_ids.*' => ['integer', 'exists:services,id'],
            'profile_photo' => ['nullable', 'file', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'nrc_front' => [Rule::requiredIf(! $user->nrc_front_path), 'file', 'mimes:jpeg,png,jpg,webp,pdf', 'max:5120'],
            'nrc_back' => [Rule::requiredIf(! $user->nrc_back_path), 'file', 'mimes:jpeg,png,jpg,webp,pdf', 'max:5120'],
            'supporting_documents' => ['nullable', 'array', 'max:5'],
            'supporting_documents.*' => ['file', 'mimes:jpeg,png,jpg,webp,pdf', 'max:5120'],
        ]);

        return DB::transaction(function () use ($user, $validated, $request) {
            $fixer = $user->fixer;

            if ($fixer && $fixer->status === 'approved') {
                abort(422, 'You are already an approved fixer.');
            }

            $updates = [];
            if ($request->hasFile('profile_photo')) {
                $path = $request->file('profile_photo')->store('fixers/profile_photos', 'public');
                $updates['profile_photo_path'] = $path;
            }
            if ($request->hasFile('nrc_front')) {
                $path = $request->file('nrc_front')->store('fixers/nrc', 'public');
                $updates['nrc_front_path'] = $path;
            }
            if ($request->hasFile('nrc_back')) {
                $path = $request->file('nrc_back')->store('fixers/nrc', 'public');
                $updates['nrc_back_path'] = $path;
            }

            $documents = (array) ($user->documents ?? []);
            if ($request->hasFile('supporting_documents')) {
                $documents = [];
                foreach ($request->file('supporting_documents') as $file) {
                    $documents[] = $file->store('fixers/documents', 'public');
                }
            }

            if (! empty($updates) || $request->hasFile('supporting_documents')) {
                $user->fill($updates);
                if (! empty($documents)) {
                    $user->documents = $documents;
                }
                $user->save();
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
