<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Support\ApiCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SubcategoryController extends Controller
{
    public function index(Request $request)
    {
        try {
            $validated = $request->validate([
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
            $perPage = max(1, min((int) ($validated['per_page'] ?? 15), 100));
            $page = max(1, (int) ($validated['page'] ?? 1));

            $key = 'subcategories:index:' . md5(http_build_query([
                'page' => $page,
                'per_page' => $perPage,
            ]));

            return ApiCache::remember(['catalog', 'subcategories'], $key, function () use ($perPage) {
                $paginator = Service::query()
                    ->active()
                    ->select('category')
                    ->whereNotNull('category')
                    ->groupBy('category')
                    ->orderBy('category')
                    ->paginate($perPage);

                $data = collect($paginator->items())->map(function ($item) {
                    $name = is_array($item) ? $item['category'] : $item->category;
                    return [
                        'id' => crc32($name),
                        'category_id' => crc32($name),
                        'name' => $name,
                        'description' => null,
                    ];
                })->values();

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

    public function show(string $subcategory)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => crc32($subcategory),
                'category_id' => crc32($subcategory),
                'name' => $subcategory,
                'description' => null,
            ],
        ]);
    }
}
