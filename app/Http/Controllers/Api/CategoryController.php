<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Support\ApiCache;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $cacheKey = 'categories:index';

        return ApiCache::remember(['catalog', 'categories'], $cacheKey, function () {
            $categories = Category::query()->latest()->get();
            return response()->json([
                'success' => true,
                'data' => $categories,
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
