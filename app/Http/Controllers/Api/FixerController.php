<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fixer;
use App\Models\User;
use App\Support\ApiCache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

        $cacheKey = 'fixers:index:' . md5($request->getQueryString() ?? 'page=1');

        return ApiCache::remember(['fixers'], $cacheKey, function () use ($q) {
            $fixers = $q->latest()->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $fixers,
            ]);
        });
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
        $cacheKey = 'fixers:top:' . $limit;

        return ApiCache::remember(['fixers', 'fixers:top'], $cacheKey, function () use ($limit) {
            $users = User::role('Fixer')
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
        });
    }

    public function current(Request $request)
    {
        $user = $request->user()->loadMissing('fixer.services');
        $fixer = $user->fixer;

        if (! $fixer) {
            return response()->json([
                'success' => true,
                'data' => null,
            ]);
        }

        $cacheKey = 'fixers:current:' . $user->id;

        return ApiCache::remember(['fixers', 'user:' . $user->id], $cacheKey, function () use ($fixer) {
            return response()->json([
                'success' => true,
                'data' => $this->formatFixer($fixer),
            ]);
        });
    }

    public function update(Request $request)
    {
        /** @var User $user */
        $user = $request->user()->loadMissing('fixer');
        $fixer = $user->fixer;

        if (! $fixer) {
            abort(404, 'Fixer profile not found.');
        }

        $validated = $request->validate([
            'bio' => ['nullable', 'string', 'max:2000'],
            'availability' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'service_ids' => ['nullable', 'array', 'min:1'],
            'service_ids.*' => ['integer', 'exists:services,id'],
        ]);

        return DB::transaction(function () use ($validated, $user, $fixer) {
            if (array_key_exists('bio', $validated)) {
                $fixer->bio = $validated['bio'];
            }

            if (array_key_exists('availability', $validated) &&
                Schema::hasColumn('fixers', 'availability')) {
                $fixer->availability = $validated['availability'];
            }

            if (! empty($validated['location'])) {
                $user->address = $validated['location'];
                $user->save();
            }

            $fixer->save();

            if (array_key_exists('service_ids', $validated)) {
                $fixer->services()->sync($validated['service_ids']);
            }

            $fixer->load(['services', 'user']);

            ApiCache::flush(['fixers', 'fixers:top', 'user:' . $user->id]);

            return response()->json([
                'success' => true,
                'data' => $this->formatFixer($fixer),
                'message' => 'Profile updated successfully.',
            ]);
        });
    }

    public function apply(Request $request)
    {
        $user = $request->user();

        $user = $request->user();

        $validated = $request->validate([
            'bio' => ['required', 'string', 'max:2000'],
            'location' => ['nullable', 'string', 'max:255'],
            'service_ids' => ['required', 'array', 'min:1'],
            'service_ids.*' => ['integer', 'exists:services,id'],
            'profile_photo' => ['required', 'file', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'nrc_front' => [Rule::requiredIf(! $user->nrc_front_path), 'file', 'mimes:jpeg,png,jpg,webp,pdf', 'max:5120'],
            'nrc_back' => [Rule::requiredIf(! $user->nrc_back_path), 'file', 'mimes:jpeg,png,jpg,webp,pdf', 'max:5120'],
            'work_photos' => ['required', 'array', 'size:3'],
            'work_photos.*' => ['file', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'supporting_documents' => ['nullable', 'array', 'max:5'],
            'supporting_documents.*' => ['file', 'mimes:jpeg,png,jpg,webp,pdf', 'max:5120'],
            'accepted_terms' => ['accepted'],
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

            $workPhotos = (array) ($user->work_photos ?? []);
            if ($request->hasFile('work_photos')) {
                $workPhotos = [];
                foreach ($request->file('work_photos') as $file) {
                    $workPhotos[] = $file->store('fixers/work_photos', 'public');
                }
            }

            $locationProvided = ! empty($validated['location']);
            if ($locationProvided) {
                $updates['address'] = $validated['location'];
            }

            if (! empty($updates) || $request->hasFile('supporting_documents') || $locationProvided) {
                $user->fill($updates);
                if (! empty($documents)) {
                    $user->documents = $documents;
                }
                if (! empty($workPhotos)) {
                    $user->work_photos = $workPhotos;
                }
                $user->save();
            }

            if (! $fixer) {
                $fixer = Fixer::create([
                    'user_id' => $user->id,
                    'bio' => $validated['bio'],
                    'status' => 'pending',
                    'accepted_terms_at' => now(),
                ]);
            } else {
                $fixer->update([
                    'bio' => $validated['bio'],
                    'status' => 'pending',
                    'accepted_terms_at' => $fixer->accepted_terms_at ?? now(),
                ]);
            }

            $fixer->services()->sync($validated['service_ids']);

            if (! $user->hasRole('Fixer')) {
                $user->assignRole('Fixer');
            }
            if (! $user->hasRole('Customer')) {
                $user->assignRole('Customer');
            }

            return response()->json([
                'success' => true,
                'data' => $fixer->load(['services', 'user']),
                'message' => 'Application submitted. We will review it shortly.',
            ]);
        });
    }

    protected function formatFixer(Fixer $fixer): array
    {
        $fixer->loadMissing(['services', 'user']);
        $user = $fixer->user;
        $resolve = static function (?string $path) {
            if (! $path) {
                return null;
            }
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path;
            }
            return Storage::disk('public')->url($path);
        };
        $mapArray = static function (?array $items) use ($resolve) {
            return collect($items ?? [])->filter()->map(fn ($p) => $resolve($p))->values()->all();
        };

        return [
            'id' => $fixer->id,
            'bio' => $fixer->bio,
            'status' => $fixer->status,
            'availability' => $fixer->availability ?? 'available',
            'rating_avg' => $fixer->rating_avg,
            'priority_points' => (int) ($fixer->priority_points ?? 0),
            'priorityPoints' => (int) ($fixer->priority_points ?? 0),
            'accepted_terms_at' => $fixer->accepted_terms_at,
            'profile_photo_url' => $resolve($user?->profile_photo_path),
            'nrc_front_url' => $resolve($user?->nrc_front_path),
            'nrc_back_url' => $resolve($user?->nrc_back_path),
            'work_photos' => $mapArray($user?->work_photos),
            'supporting_documents' => $mapArray($user?->documents),
            'services' => $fixer->services->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'price' => $service->price,
                ];
            })->values()->all(),
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'profile_photo_url' => $user->profile_photo_url,
                'nrc_front_path' => $user->nrc_front_path,
                'nrc_back_path' => $user->nrc_back_path,
                'work_photos' => $user->work_photos,
                'documents' => $user->documents,
            ],
        ];
    }
}
