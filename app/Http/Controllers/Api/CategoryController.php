<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Support\ApiCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
                        'id' => crc32($name),
                        'name' => $name,
                        'description' => null,
                    ];
                })->values();

                return response()->json([
                    'success' => true,
                    'data' => $data,
                    'meta' => [
                        'current_page' => $categories->currentPage(),
                        'per_page' => $categories->perPage(),
                        'total' => $categories->total(),
                        'last_page' => $categories->lastPage(),
                        'from' => $categories->firstItem(),
                        'to' => $categories->lastItem(),
                    ],
                    'links' => [
                        'first' => $categories->url(1),
                        'last' => $categories->url($categories->lastPage()),
                        'prev' => $categories->previousPageUrl(),
                        'next' => $categories->nextPageUrl(),
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

    public function show(string $category)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => crc32($category),
                'name' => $category,
                'description' => null,
            ],
        ]);
    }

    public function subcategories(string $category)
    {
        return response()->json([
            'success' => true,
            'data' => [],
        ]);
    }
}
