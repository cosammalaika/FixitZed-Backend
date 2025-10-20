<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Support\ApiCache;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Service::query();
        if ($request->filled('subcategory_id')) {
            $query->where('subcategory_id', $request->integer('subcategory_id'));
        }
        if ($request->filled('category_id')) {
            $query->whereHas('subcategory', function ($q) use ($request) {
                $q->where('category_id', $request->integer('category_id'));
            });
        }
        $key = 'services:index:' . md5($request->getQueryString() ?? 'all');

        return ApiCache::remember(['catalog', 'services'], $key, function () use ($query) {
            $services = $query->latest()->get();
            return response()->json([
                'success' => true,
                'data' => $services,
            ]);
        });
    }

    public function show(Service $service)
    {
        $service->load('subcategory');
        return response()->json([
            'success' => true,
            'data' => $service,
        ]);
    }
}
