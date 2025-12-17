<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

trait ResolvesPerPage
{
    protected function resolvePerPage(
        Request $request,
        string $settingKey = 'api.per_page_default',
        int $default = 20,
        int $min = 1,
        int $max = 100
    ): int {
        $fallback = (int) setting('api.per_page_default', $default);
        $base = (int) setting($settingKey, $fallback);
        $perPage = (int) $request->integer('per_page', $base);
        return max($min, min($perPage, $max));
    }
}
