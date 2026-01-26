<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Support\ApiCache;
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
            $query = Service::query()->select([
                'id',
                'name',
                'category',
                'description',
                'is_active',
                'created_at',
                'updated_at',
            ]);

            // Always return only active services; avoid any legacy category/subcategory filters
            $query->active();

            if (! empty($validated['search'])) {
                $term = '%' . trim($validated['search']) . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', $term)
                        ->orWhere('description', 'like', $term);
                });
            }

            $perPage = max(1, min((int) ($validated['per_page'] ?? 20), 100));
            $page = max(1, (int) ($validated['page'] ?? 1));

            $key = 'services:index:' . md5(http_build_query([
                'page' => $page,
                'per_page' => $perPage,
                'search' => $request->input('search'),
            ]));

            return ApiCache::remember(['catalog', 'services'], $key, function () use ($query, $perPage) {
                $services = $query
                    ->orderBy('services.name')
                    ->get();

                return response()->json([
                    'success' => true,
                    'data' => $services->values(),
                    'meta' => [
                        'count' => $services->count(),
                    ],
                ]);
            });
        } catch (\Throwable $e) {
            Log::error('Service index failed', [
                'search' => $request->input('search'),
                'status' => $request->input('status'),
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
            'data' => $service,
            'meta' => [
                'count' => 1,
            ],
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

    public function active(Request $request)
    {
        $request->merge(['status' => 'active']);
        return $this->index($request);
    }
}
