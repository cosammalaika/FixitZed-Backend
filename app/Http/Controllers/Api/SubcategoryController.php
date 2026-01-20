<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subcategory;
use App\Support\ApiCache;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SubcategoryController extends Controller
{
    public function index(Request $request)
    {
        try {
            $validated = $request->validate([
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
            $q = Subcategory::query()->select('id', 'category_id', 'name', 'description', 'created_at', 'updated_at');
            if (! empty($validated['category_id'])) {
                $q->where('category_id', (int) $validated['category_id']);
            }
            $dedupedIds = Subcategory::query()
                ->selectRaw('MIN(id) as id')
                ->groupBy('name', 'category_id');
            $q->whereIn('id', $dedupedIds);
            $perPage = max(1, min((int) ($validated['per_page'] ?? 15), 100));
            $page = max(1, (int) ($validated['page'] ?? 1));

            $key = 'subcategories:index:' . md5(http_build_query([
                'page' => $page,
                'per_page' => $perPage,
                'category_id' => $request->input('category_id'),
            ]));

            return ApiCache::remember(['catalog', 'subcategories'], $key, function () use ($q, $perPage) {
                $this->seedCatalogIfMissing();

                $paginator = $q->orderBy('name')->paginate($perPage);
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
            Log::error('Subcategory list failed', [
                'category_id' => $request->input('category_id'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to load subcategories right now.',
                'error_code' => 'SUBCATEGORY_LIST_FAILED',
            ], 503);
        }
    }

    public function show(Subcategory $subcategory)
    {
        $subcategory->load('category');
        return response()->json([
            'success' => true,
            'data' => $subcategory,
        ]);
    }

    protected function seedCatalogIfMissing(): void
    {
        if (! Subcategory::query()->exists()) {
            Log::info('Seeding subcategories (bootstrap)');
            (new ServiceCatalogSeeder())->run();
            ApiCache::flush(['catalog', 'categories', 'subcategories', 'services']);
        }
    }
}
