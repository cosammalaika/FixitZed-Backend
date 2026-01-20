<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Support\ApiCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Database\Seeders\ServiceCatalogSeeder;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = max(1, min((int) $request->integer('per_page', 15), 100));
            $page = max(1, $request->integer('page', 1));

            $cacheKey = 'categories:index:' . md5(http_build_query([
                'page' => $page,
                'per_page' => $perPage,
            ]));

            return ApiCache::remember(['catalog', 'categories'], $cacheKey, function () use ($perPage) {
                $this->seedCatalogIfMissing();

                $dedupedIds = Category::query()
                    ->selectRaw('MIN(id) as id')
                    ->groupBy('name');

                $paginator = Category::query()
                    ->select('id', 'name', 'description', 'created_at', 'updated_at')
                    ->whereIn('id', $dedupedIds)
                    ->orderBy('name')
                    ->paginate($perPage);

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
            Log::error('Category list failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to load categories right now.',
                'error_code' => 'CATEGORY_LIST_FAILED',
            ], 503);
        }
    }

    public function show(Category $category)
    {
        return response()->json([
            'success' => true,
            'data' => $category,
        ]);
    }

    public function subcategories(Category $category)
    {
        $category->load('subcategories');
        return response()->json([
            'success' => true,
            'data' => $category->subcategories,
        ]);
    }

    protected function seedCatalogIfMissing(): void
    {
        if (! Category::query()->exists()) {
            Log::info('Seeding categories (bootstrap)');
            (new ServiceCatalogSeeder())->run();
            ApiCache::flush(['catalog', 'categories', 'subcategories', 'services']);
        }
    }
}
