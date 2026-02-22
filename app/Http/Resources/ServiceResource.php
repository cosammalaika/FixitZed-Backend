<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $service = $this->resource;
        $attributes = method_exists($service, 'getAttributes') ? $service->getAttributes() : [];

        $subcategory = method_exists($service, 'relationLoaded') && $service->relationLoaded('subcategory')
            ? $service->subcategory
            : null;

        $categoryModel = null;
        if ($subcategory && method_exists($subcategory, 'relationLoaded') && $subcategory->relationLoaded('category')) {
            $categoryModel = $subcategory->category;
        }

        $isActive = (bool) ($service->is_active ?? false);
        $status = ($service->status ?? 'inactive') === 'active' ? 'active' : 'inactive';

        $legacyCategory = null;
        if (array_key_exists('category', $attributes) && is_string($attributes['category']) && trim($attributes['category']) !== '') {
            $legacyCategory = trim($attributes['category']);
        } elseif ($subcategory && isset($subcategory->name) && is_string($subcategory->name) && trim($subcategory->name) !== '') {
            // Preserve legacy mobile/admin grouping behavior where `category` was a plain label like "Plumbing".
            $legacyCategory = trim($subcategory->name);
        }

        $fixersCount = array_key_exists('fixers_count', $attributes)
            ? (int) $service->fixers_count
            : null;

        $subcategoryId = $service->subcategory_id ?? ($subcategory->id ?? null);
        $categoryId = $categoryModel->id ?? ($subcategory->category_id ?? null);

        return [
            'id' => isset($service->id) ? (int) $service->id : null,
            'name' => $service->name ?? null,
            'description' => $service->description ?? null,
            'category' => $legacyCategory,
            'category_id' => $categoryId !== null ? (int) $categoryId : null,
            'category_name' => $categoryModel->name ?? null,
            'subcategory_id' => $subcategoryId !== null ? (int) $subcategoryId : null,
            'subcategory_name' => $subcategory->name ?? null,
            'price' => array_key_exists('price', $attributes) || isset($service->price)
                ? ($service->price !== null ? (float) $service->price : null)
                : null,
            'duration_minutes' => array_key_exists('duration_minutes', $attributes) || isset($service->duration_minutes)
                ? ($service->duration_minutes !== null ? (int) $service->duration_minutes : null)
                : null,
            'is_active' => $isActive,
            'status' => $status,
            'active' => $isActive,
            'fixers_count' => $fixersCount,
            'created_at' => $service->created_at ?? null,
            'updated_at' => $service->updated_at ?? null,
        ];
    }
}
