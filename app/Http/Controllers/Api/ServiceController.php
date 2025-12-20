<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Support\ApiCache;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Service::query();

        if ($request->filled('search')) {
            $term = '%' . trim($request->input('search')) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('description', 'like', $term);
            });
        }

        if ($request->filled('subcategory_id')) {
            $query->where('subcategory_id', $request->integer('subcategory_id'));
        }
        if ($request->filled('category_id')) {
            $query->whereHas('subcategory', function ($q) use ($request) {
                $q->where('category_id', $request->integer('category_id'));
            });
        }
        $perPage = (int) $request->integer('per_page', 20);
        $perPage = max(1, min($perPage, 100));
        $page = max(1, $request->integer('page', 1));

        $key = 'services:index:' . md5(http_build_query([
            'page' => $page,
            'per_page' => $perPage,
            'subcategory_id' => $request->input('subcategory_id'),
            'category_id' => $request->input('category_id'),
        ]));

        return ApiCache::remember(['catalog', 'services'], $key, function () use ($query, $perPage) {
            if (! Service::query()->exists()) {
                (new ServiceCatalogSeeder())->run();
            }

            $paginator = $query
                ->with(['subcategory', 'subcategory.category'])
                ->latest()
                ->paginate($perPage);
            return response()->json([
                'success' => true,
                'data' => $paginator->items(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'last_page' => $paginator->lastPage(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
                'links' => [
                    'first' => $paginator->url(1),
                    'last' => $paginator->url($paginator->lastPage()),
                    'prev' => $paginator->previousPageUrl(),
                    'next' => $paginator->nextPageUrl(),
                ],
            ]);
        });
    }

    public function show(Service $service)
    {
        $service->load('subcategory');
        return response()->json([
            'success' => true,
            'data' => $service,
        ]);
    }

    public function fixers(Request $request, Service $service)
    {
        try {
            $fixers = $service->fixers()
                ->with('user')
                ->whereHas('user', function ($q) {
                    $q->where('status', 'Active')->whereNotNull('email_verified_at');
                })
                ->where(function ($q) {
                    $q->whereNull('status')->orWhere('status', 'Active');
                })
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
            ], 503);
        }
    }
}
