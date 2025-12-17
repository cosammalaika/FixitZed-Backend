<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\ResolvesPerPage;
use App\Models\Subcategory;
use App\Support\ApiCache;
use Illuminate\Http\Request;

class SubcategoryController extends Controller
{
    use ResolvesPerPage;

    public function index(Request $request)
    {
        $q = Subcategory::query();
        if ($request->filled('category_id')) {
            $q->where('category_id', $request->integer('category_id'));
        }
        $perPage = $this->resolvePerPage($request);
        $page = max(1, $request->integer('page', 1));

        $key = 'subcategories:index:' . md5(http_build_query([
            'page' => $page,
            'per_page' => $perPage,
            'category_id' => $request->input('category_id'),
        ]));

        return ApiCache::remember(['catalog', 'subcategories'], $key, function () use ($q, $perPage) {
            $paginator = $q->latest()->paginate($perPage);
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

    public function show(Subcategory $subcategory)
    {
        $subcategory->load('category');
        return response()->json([
            'success' => true,
            'data' => $subcategory,
        ]);
    }
}
