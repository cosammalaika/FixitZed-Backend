<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Service;
use App\Models\Subcategory;
use App\Support\ApiCache;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        try {
            $validated = $request->validate([
                'search' => 'nullable|string|max:255',
                'subcategory_id' => 'nullable|integer',
                'category_id' => 'nullable|integer',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:100',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid filters provided.',
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            $query = Service::query()
                ->select('services.*')
                ->with(['subcategory:id,category_id,name', 'subcategory.category:id,name']);

            if (! empty($validated['search'])) {
                $term = '%' . trim($validated['search']) . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', $term)
                        ->orWhere('description', 'like', $term);
                });
            }

            if (! empty($validated['subcategory_id'])) {
                $query->where('subcategory_id', (int) $validated['subcategory_id']);
            }

            if (! empty($validated['category_id'])) {
                $query->whereHas('subcategory', function ($q) use ($validated) {
                    $q->where('category_id', (int) $validated['category_id']);
                });
            }

            // Deduplicate by name/subcategory, keeping the earliest id to avoid dropdown repeats.
            $dedupedIds = Service::query()
                ->selectRaw('MIN(id) as id')
                ->groupBy('name', 'subcategory_id');

            $query->whereIn('services.id', $dedupedIds);

            $perPage = max(1, min((int) ($validated['per_page'] ?? 20), 100));
            $page = max(1, (int) ($validated['page'] ?? 1));

            $key = 'services:index:' . md5(http_build_query([
                'page' => $page,
                'per_page' => $perPage,
                'subcategory_id' => $request->input('subcategory_id'),
                'category_id' => $request->input('category_id'),
                'search' => $request->input('search'),
            ]));

            return ApiCache::remember(['catalog', 'services'], $key, function () use ($query, $perPage) {
                $paginator = $query
                    ->distinct()
                    ->orderBy('services.name')
                    ->paginate($perPage);

                $isEmpty = $paginator->isEmpty();
                if ($isEmpty) {
                    Log::warning('Services index returned empty list', [
                        'filters' => request()->only(['search', 'category_id', 'subcategory_id']),
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'data' => array_values($paginator->items()),
                    'meta' => [
                        'current_page' => $paginator->currentPage(),
                        'per_page' => $paginator->perPage(),
                        'total' => $paginator->total(),
                        'last_page' => $paginator->lastPage(),
                        'from' => $paginator->firstItem(),
                        'to' => $paginator->lastItem(),
                        'is_empty' => $isEmpty,
                    ],
                    'links' => [
                        'first' => $paginator->url(1),
                        'last' => $paginator->url($paginator->lastPage()),
                        'prev' => $paginator->previousPageUrl(),
                        'next' => $paginator->nextPageUrl(),
                    ],
                ]);
            });
        } catch (\Throwable $e) {
            Log::error('Service index failed', [
                'category_id' => $request->input('category_id'),
                'subcategory_id' => $request->input('subcategory_id'),
                'search' => $request->input('search'),
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
        $service->load(['subcategory', 'subcategory.category']);
        return response()->json([
            'success' => true,
            'data' => $service,
        ]);
    }

    public function fixers(Request $request, Service $service)
    {
        try {
            $fixers = $service->fixers()
                ->select('fixers.id', 'fixers.user_id', 'fixers.rating_avg', 'fixers.status')
                ->with('user')
                ->whereHas('user', function ($q) {
                    $q->where('status', 'Active')->whereNotNull('email_verified_at');
                })
                ->where(function ($q) {
                    $q->whereNull('status')->orWhere('status', 'Active');
                })
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

    protected function seedCatalogIfMissing(): void
    {
        if (! Category::query()->exists() || ! Subcategory::query()->exists() || ! Service::query()->exists()) {
            Log::warning('Service catalog is empty. Please seed via artisan command; skipping auto-seed on GET.');
        }
    }
}
