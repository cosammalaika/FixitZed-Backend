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

        $categoryLabel = null;
        if (array_key_exists('category', $attributes) && is_string($attributes['category']) && trim($attributes['category']) !== '') {
            $categoryLabel = trim($attributes['category']);
        } elseif (isset($service->category) && is_string($service->category) && trim($service->category) !== '') {
            $categoryLabel = trim($service->category);
        }

        if (! $categoryLabel) {
            $subcategory = method_exists($service, 'relationLoaded') && $service->relationLoaded('subcategory')
                ? $service->subcategory
                : null;

            if ($subcategory && isset($subcategory->name) && is_string($subcategory->name) && trim($subcategory->name) !== '') {
                $categoryLabel = trim($subcategory->name);
            }
        }

        $categoryLabel ??= 'General';

        $catalogId = static::catalogAliasId($categoryLabel);
        $isActive = (bool) ($service->is_active ?? false);
        $status = ($service->status ?? 'inactive') === 'active' ? 'active' : 'inactive';

        $fixersCount = array_key_exists('fixers_count', $attributes)
            ? (int) $service->fixers_count
            : null;

        return [
            'id' => isset($service->id) ? (int) $service->id : null,
            'name' => $service->name ?? null,
            'category' => $categoryLabel,
            'description' => $service->description ?? null,
            'is_active' => $isActive,

            // Compatibility aliases for current mobile/admin clients.
            'status' => $status,
            'active' => $isActive,
            'category_id' => $catalogId,
            'category_name' => $categoryLabel,
            'subcategory_id' => $catalogId,
            'subcategory_name' => $categoryLabel,
            'fixers_count' => $fixersCount,
        ];
    }

    public static function catalogAliasId(string $label): int
    {
        $normalized = mb_strtolower(trim($label));
        $unsigned = sprintf('%u', crc32('svc-category:' . $normalized));

        return (int) $unsigned;
    }
}
