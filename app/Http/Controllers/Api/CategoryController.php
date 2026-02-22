<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Service;
use App\Models\Subcategory;
use App\Support\ApiCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

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
                if (Schema::hasTable('categories')) {
                    $categories = Category::query()
                        ->orderBy('name')
                        ->paginate($perPage);

                    $data = collect($categories->items())
                        ->map(fn (Category $category) => $this->formatCategory($category))
                        ->values();

                    return $this->paginatedResponse($categories, $data);
                }

                if (! Schema::hasColumn('services', 'category')) {
                    return response()->json([
                        'success' => true,
                        'data' => [],
                        'meta' => [
                            'current_page' => 1,
                            'per_page' => $perPage,
                            'total' => 0,
                            'last_page' => 1,
                            'from' => null,
                            'to' => null,
                        ],
                        'links' => [
                            'first' => null,
                            'last' => null,
                            'prev' => null,
                            'next' => null,
                        ],
                    ]);
                }

                $categories = Service::query()
                    ->active()
                    ->select('category')
                    ->whereNotNull('category')
                    ->groupBy('category')
                    ->orderBy('category')
                    ->paginate($perPage);

                $data = collect($categories->items())->map(function ($item) {
                    $name = is_array($item) ? $item['category'] : $item->category;

                    return [
                        'id' => (int) crc32((string) $name),
                        'name' => $name,
                        'description' => null,
                    ];
                })->values();

                return $this->paginatedResponse($categories, $data);
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

    public function show(string $category)
    {
        if (Schema::hasTable('categories')) {
            $query = Category::query();

            if (is_numeric($category)) {
                $query->where('id', (int) $category)->orWhere('name', $category);
            } else {
                $query->where('name', $category);
            }

            $record = $query->first();

            if ($record) {
                return response()->json([
                    'success' => true,
                    'data' => $this->formatCategory($record),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => (int) crc32($category),
                'name' => $category,
                'description' => null,
            ],
        ]);
    }

    public function subcategories(string $category)
    {
        if (Schema::hasTable('categories') && Schema::hasTable('subcategories')) {
            $categoryQuery = Category::query();

            if (is_numeric($category)) {
                $categoryQuery->where('id', (int) $category)->orWhere('name', $category);
            } else {
                $categoryQuery->where('name', $category);
            }

            $record = $categoryQuery->first();

            if ($record) {
                $data = Subcategory::query()
                    ->where('category_id', $record->id)
                    ->orderBy('name')
                    ->get()
                    ->map(fn (Subcategory $subcategory) => [
                        'id' => (int) $subcategory->id,
                        'category_id' => (int) $subcategory->category_id,
                        'name' => $subcategory->name,
                        'description' => $subcategory->description,
                    ])
                    ->values();

                return response()->json([
                    'success' => true,
                    'data' => $data,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'data' => [],
        ]);
    }

    protected function formatCategory(Category $category): array
    {
        return [
            'id' => (int) $category->id,
            'name' => $category->name,
            'description' => $category->description,
        ];
    }

    protected function paginatedResponse($paginator, $data)
    {
        return response()->json([
            'success' => true,
            'data' => $data,
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
    }
}
