<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subcategory;
use App\Support\ApiCache;
use Illuminate\Http\Request;

class SubcategoryController extends Controller
{
    public function index(Request $request)
    {
        $q = Subcategory::query();
        if ($request->filled('category_id')) {
            $q->where('category_id', $request->integer('category_id'));
        }
        $key = 'subcategories:index:' . md5($request->getQueryString() ?? 'all');

        return ApiCache::remember(['catalog', 'subcategories'], $key, function () use ($q) {
            return response()->json([
                'success' => true,
                'data' => $q->latest()->get(),
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
