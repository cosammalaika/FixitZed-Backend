<?php

namespace App\Support;

use App\Models\Province;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

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

        try {
            if (! Schema::hasTable('provinces')) {
                return $fallback;
            }
        } catch (\Throwable $e) {
            report($e);
            return $fallback;
        }

        if (Cache::has(self::CACHE_KEY)) {
            $cached = Cache::get(self::CACHE_KEY);
            if (is_array($cached) && ! empty($cached)) {
                return $cached;
            }

            Cache::forget(self::CACHE_KEY);
        }

        $map = Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, function () use ($fallback) {
            try {
                $provinces = Province::with(['districts' => function ($query) {
                    $query->orderBy('name');
                }])
                    ->orderBy('name')
                    ->get();
            } catch (\Throwable $e) {
                report($e);
                return $fallback;
            }

            if ($provinces->isEmpty()) {
                return $fallback;
            }

            $mapped = $provinces
                ->mapWithKeys(static function (Province $province) {
                    $name = is_string($province->name) ? trim($province->name) : '';
                    if ($name === '') {
                        return [];
                    }

                    $districts = $province->districts
                        ->pluck('name')
                        ->filter(static fn ($value) => is_string($value) && trim($value) !== '')
                        ->map(static fn ($value) => trim($value))
                        ->unique()
                        ->sort()
                        ->values()
                        ->all();

                    return [$name => $districts];
                })
                ->filter(static fn ($districts) => ! empty($districts))
                ->toArray();

            if (empty($mapped)) {
                return $fallback;
            }

            foreach ($fallback as $province => $districts) {
                if (! array_key_exists($province, $mapped) || empty($mapped[$province])) {
                    $mapped[$province] = $districts;
                }
            }

            ksort($mapped, SORT_NATURAL | SORT_FLAG_CASE);

            return $mapped;
        });

        if (empty($map) && ! empty($fallback)) {
            Cache::put(self::CACHE_KEY, $fallback, self::CACHE_TTL_SECONDS);
            return $fallback;
        }

        return is_array($map) ? $map : $fallback;
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
        $raw = config('provinces.map', null);

        if (is_null($raw)) {
            $path = config_path('provinces.php');
            if (is_string($path) && file_exists($path)) {
                $data = include $path;
                if (is_array($data) && array_key_exists('map', $data)) {
                    $raw = $data['map'];
                }
            }
        }

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
