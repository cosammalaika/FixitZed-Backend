<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\Category;
use App\Models\Service;
use App\Models\Subcategory;
use App\Support\ApiCache;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
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

            return ApiCache::remember(['catalog', 'categories'], $cacheKey, function () use ($page, $perPage) {
                $derived = $this->derivedCategories();
                if ($derived->isNotEmpty()) {
                    return $this->paginatedCollectionResponse($derived, $page, $perPage);
                }

                if (Schema::hasTable('categories')) {
                    $categories = Category::query()
                        ->orderBy('name')
                        ->paginate($perPage, ['*'], 'page', $page);

                    $data = collect($categories->items())
                        ->map(fn (Category $category) => $this->formatCategory($category))
                        ->values();

                    return $this->paginatedResponse($categories, $data);
                }

                return $this->paginatedCollectionResponse(collect(), $page, $perPage);
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
        $derived = $this->derivedCategories();
        if ($derived->isNotEmpty()) {
            $record = $this->findDerivedCategory($derived, $category);

            if ($record) {
                return response()->json([
                    'success' => true,
                    'data' => $record,
                ]);
            }
        }

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
                'id' => ServiceResource::catalogAliasId($category),
                'name' => $category,
                'description' => null,
            ],
        ]);
    }

    public function subcategories(string $category)
    {
        $derived = $this->derivedCategories();
        if ($derived->isNotEmpty()) {
            $record = $this->findDerivedCategory($derived, $category);

            if (! $record) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [[
                    'id' => (int) $record['id'],
                    'category_id' => (int) $record['id'],
                    'name' => $record['name'],
                    'description' => null,
                ]],
            ]);
        }

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

    protected function derivedCategories(): Collection
    {
        if (! Schema::hasTable('services') || ! Schema::hasColumn('services', 'category')) {
            return collect();
        }

        return Service::query()
            ->select('category')
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->map(fn ($label) => trim((string) $label))
            ->filter(fn (string $label) => $label !== '')
            ->values()
            ->map(fn (string $label) => [
                'id' => ServiceResource::catalogAliasId($label),
                'name' => $label,
                'description' => null,
            ]);
    }

    protected function findDerivedCategory(Collection $items, string $category): ?array
    {
        if (is_numeric($category)) {
            return $items->first(fn (array $item) => (int) $item['id'] === (int) $category);
        }

        $needle = mb_strtolower(trim($category));

        return $items->first(fn (array $item) => mb_strtolower((string) $item['name']) === $needle);
    }

    protected function formatCategory(Category $category): array
    {
        return [
            'id' => (int) $category->id,
            'name' => $category->name,
            'description' => $category->description,
        ];
    }

    protected function paginatedCollectionResponse(Collection $items, int $page, int $perPage)
    {
        $total = $items->count();
        $slice = $items->forPage($page, $perPage)->values();
        $paginator = new LengthAwarePaginator(
            $slice,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return $this->paginatedResponse($paginator, $slice);
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
