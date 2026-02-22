<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use App\Support\ApiCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        try {
            $validated = $request->validate([
                'search' => 'nullable|string|max:255',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid filters provided.',
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            $query = Service::query()->select($this->serviceSelectColumns());

            if (Schema::hasTable('fixer_service')) {
                $pivotHasStatus = Schema::hasColumn('fixer_service', 'status');

                $query->withCount([
                    'fixers as fixers_count' => function ($q) use ($pivotHasStatus) {
                        if ($pivotHasStatus) {
                            $q->where(function ($q2) {
                                $q2->whereNull('fixer_service.status')
                                    ->orWhere('fixer_service.status', 'Active');
                            });
                        }

                        $q->whereHas('user', function ($q3) {
                            $q3->where('status', 'Active')
                                ->whereNotNull('email_verified_at');
                        });
                    },
                ]);
            }

            if ($request->boolean('only_active')) {
                $query->active();
            }

            if (! empty($validated['search'])) {
                $term = '%' . trim($validated['search']) . '%';
                $hasDescription = Schema::hasColumn('services', 'description');
                $hasCategory = Schema::hasColumn('services', 'category');

                $query->where(function ($q) use ($term, $hasDescription, $hasCategory) {
                    $q->where('name', 'like', $term);

                    if ($hasDescription) {
                        $q->orWhere('description', 'like', $term);
                    }

                    if ($hasCategory) {
                        $q->orWhere('category', 'like', $term);
                    }
                });
            }

            $perPage = max(1, min((int) ($validated['per_page'] ?? 20), 100));
            $page = max(1, (int) ($validated['page'] ?? 1));

            $key = 'services:index:' . md5(http_build_query([
                'page' => $page,
                'per_page' => $perPage,
                'search' => $request->input('search'),
                'only_active' => $request->boolean('only_active'),
            ]));

            return ApiCache::remember(['catalog', 'services'], $key, function () use ($query) {
                $services = $query
                    ->orderBy('services.name')
                    ->get();

                return response()->json([
                    'success' => true,
                    'data' => ServiceResource::collection($services)->resolve(),
                    'meta' => [
                        'count' => $services->count(),
                    ],
                ]);
            });
        } catch (\Throwable $e) {
            Log::error('Service index failed', [
                'search' => $request->input('search'),
                'only_active' => $request->boolean('only_active'),
                'query' => $request->query(),
                'exception' => get_class($e),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to load services right now.',
                'error_code' => 'SERVICE_LIST_FAILED',
            ], 503);
        }
    }

    public function show(Service $service)
    {
        return response()->json([
            'success' => true,
            'data' => (new ServiceResource($service))->resolve(),
            'meta' => [
                'count' => 1,
            ],
        ]);
    }

    public function fixers(Request $request, Service $service)
    {
        try {
            $pivotHasStatus = Schema::hasTable('fixer_service') && Schema::hasColumn('fixer_service', 'status');

            $fixersQuery = $service->fixers()
                ->select('fixers.id', 'fixers.user_id', 'fixers.rating_avg', 'fixers.status')
                ->with('user')
                ->whereHas('user', function ($q) {
                    $q->where('status', 'Active')->whereNotNull('email_verified_at');
                });

            if ($pivotHasStatus) {
                $fixersQuery->where(function ($q) {
                    $q->whereNull('fixer_service.status')->orWhere('fixer_service.status', 'Active');
                });
            }

            $fixers = $fixersQuery
                ->distinct()
                ->get()
                ->map(function ($fixer) {
                    return [
                        'id' => $fixer->id,
                        'user_id' => $fixer->user_id,
                        'name' => trim($fixer->user->first_name . ' ' . $fixer->user->last_name),
                        'rating_avg' => $fixer->rating_avg,
                        'status' => $fixer->status,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $fixers,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Service fixers lookup failed', [
                'service_id' => $service->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to load fixers right now.',
                'error_code' => 'SERVICE_FIXERS_FAILED',
            ], 503);
        }
    }

    public function active(Request $request)
    {
        $request->merge(['only_active' => true]);

        return $this->index($request);
    }

    protected function serviceSelectColumns(): array
    {
        $columns = ['id', 'name'];

        foreach (['category', 'description', 'is_active', 'created_at', 'updated_at'] as $column) {
            if (Schema::hasColumn('services', $column)) {
                $columns[] = $column;
            }
        }

        return $columns;
    }
}
