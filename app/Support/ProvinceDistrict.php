<?php

namespace App\Support;

use App\Models\Province;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ProvinceDistrict
{
    private const CACHE_KEY = 'province_district_map';
    private const CACHE_TTL_SECONDS = 3600;

    /**
     * Returns an alphabetized map of province => districts[]
     *
     * @return array<string, array<int, string>>
     */
    public static function map(): array
    {
        $fallback = static::configMap();

        if (! Schema::hasTable('provinces')) {
            return $fallback;
        }

        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, function () use ($fallback) {
            $provinces = Province::with(['districts' => function ($query) {
                $query->orderBy('name');
            }])
                ->orderBy('name')
                ->get();

            if ($provinces->isEmpty()) {
                return $fallback;
            }

            return $provinces
                ->mapWithKeys(static function (Province $province) {
                    return [
                        $province->name => $province->districts
                            ->pluck('name')
                            ->filter(static fn ($name) => is_string($name) && $name !== '')
                            ->map(static fn ($name) => trim($name))
                            ->unique()
                            ->sort()
                            ->values()
                            ->all(),
                    ];
                })
                ->filter(static fn ($districts) => ! empty($districts))
                ->toArray();
        });
    }

    /**
     * Forget cached map and rebuild.
     */
    public static function refresh(): array
    {
        Cache::forget(self::CACHE_KEY);
        return self::map();
    }

    /**
     * Map from config fallback.
     *
     * @return array<string, array<int, string>>
     */
    private static function configMap(): array
    {
        $raw = config('provinces.map', []);
        if (! is_array($raw) || empty($raw)) {
            return [];
        }

        $normalized = [];
        foreach ($raw as $province => $districts) {
            if (! is_string($province)) {
                continue;
            }
            $provinceName = trim($province);
            if ($provinceName === '') {
                continue;
            }

            $list = [];
            foreach ((array) $districts as $district) {
                if (! is_string($district)) {
                    continue;
                }
                $districtName = trim($district);
                if ($districtName === '') {
                    continue;
                }

                $list[$districtName] = $districtName;
            }

            if (! empty($list)) {
                ksort($list, SORT_NATURAL | SORT_FLAG_CASE);
                $normalized[$provinceName] = array_values($list);
            }
        }

        if (empty($normalized)) {
            return [];
        }

        ksort($normalized, SORT_NATURAL | SORT_FLAG_CASE);

        return $normalized;
    }
}
