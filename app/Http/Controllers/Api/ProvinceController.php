<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Province;
use Illuminate\Http\JsonResponse;

class ProvinceController extends Controller
{
    public function index(): JsonResponse
    {
        $provinces = Province::with(['districts' => function ($query) {
            $query->orderBy('name');
        }])
            ->orderBy('name')
            ->get();

        $data = $provinces->map(static function (Province $province) {
            return [
                'id' => $province->id,
                'name' => $province->name,
                'slug' => $province->slug,
                'districts' => $province->districts
                    ->map(static function ($district) {
                        return [
                            'id' => $district->id,
                            'name' => $district->name,
                            'slug' => $district->slug,
                        ];
                    })
                    ->values()
                    ->all(),
            ];
        })->values()->all();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
