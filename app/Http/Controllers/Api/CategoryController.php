<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\ResolvesPerPage;
use App\Models\Category;
use App\Support\ApiCache;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use ResolvesPerPage;

    public function index(Request $request)
    {
        $perPage = $this->resolvePerPage($request);
        $page = max(1, $request->integer('page', 1));

        $cacheKey = 'categories:index:' . md5(http_build_query([
            'page' => $page,
            'per_page' => $perPage,
        ]));

        return ApiCache::remember(['catalog', 'categories'], $cacheKey, function () use ($perPage) {
            $paginator = Category::query()
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
}
