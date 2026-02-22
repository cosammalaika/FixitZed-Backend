<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Subcategory;
use App\Support\ApiCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class SubcategoryController extends Controller
{
    public function index(Request $request)
    {
        try {
            $validated = $request->validate([
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:100',
                'category_id' => 'nullable|integer|min:1',
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
            $categoryId = isset($validated['category_id']) ? (int) $validated['category_id'] : null;

            $key = 'subcategories:index:' . md5(http_build_query([
                'page' => $page,
                'per_page' => $perPage,
                'category_id' => $categoryId,
            ]));

            return ApiCache::remember(['catalog', 'subcategories'], $key, function () use ($perPage, $categoryId) {
                if (Schema::hasTable('subcategories')) {
                    $paginator = Subcategory::query()
                        ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
                        ->orderBy('name')
                        ->paginate($perPage);

                    $data = collect($paginator->items())
                        ->map(fn (Subcategory $subcategory) => $this->formatSubcategory($subcategory))
                        ->values();

                    return $this->paginatedResponse($paginator, $data);
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

                $paginator = Service::query()
                    ->active()
                    ->select('category')
                    ->whereNotNull('category')
                    ->groupBy('category')
                    ->orderBy('category')
                    ->paginate($perPage);

                $data = collect($paginator->items())->map(function ($item) {
                    $name = is_array($item) ? $item['category'] : $item->category;
                    $id = (int) crc32((string) $name);

                    return [
                        'id' => $id,
                        'category_id' => $id,
                        'name' => $name,
                        'description' => null,
                    ];
                })->values();

                return $this->paginatedResponse($paginator, $data);
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
        if (Schema::hasTable('subcategories')) {
            $query = Subcategory::query();

            if (is_numeric($subcategory)) {
                $query->where('id', (int) $subcategory)->orWhere('name', $subcategory);
            } else {
                $query->where('name', $subcategory);
            }

            $record = $query->first();

            if ($record) {
                return response()->json([
                    'success' => true,
                    'data' => $this->formatSubcategory($record),
                ]);
            }
        }

        $id = (int) crc32($subcategory);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $id,
                'category_id' => $id,
                'name' => $subcategory,
                'description' => null,
            ],
        ]);
    }

    protected function formatSubcategory(Subcategory $subcategory): array
    {
        return [
            'id' => (int) $subcategory->id,
            'category_id' => (int) $subcategory->category_id,
            'name' => $subcategory->name,
            'description' => $subcategory->description,
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
