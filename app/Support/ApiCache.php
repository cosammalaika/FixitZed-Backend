<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class ApiCache
{
    public static function remember(array $tags, string $key, callable $callback)
    {
        if (! self::enabled() || ! self::supportsTags()) {
            return $callback();
        }

        $cached = Cache::tags($tags)->get($key);
        if ($cached !== null) {
            return self::unpack($cached);
        }

        $value = $callback();
        Cache::tags($tags)->put($key, self::pack($value), now()->addSeconds(self::ttl()));

        return $value;
    }

    public static function flush(array $tags): void
    {
        if (! self::enabled() || ! self::supportsTags()) {
            return;
        }

        Cache::tags($tags)->flush();
    }

    public static function enabled(): bool
    {
        return (bool) config('performance.api_cache_enabled', false);
    }

    public static function ttl(): int
    {
        return (int) config('performance.api_cache_ttl', 300);
    }

    protected static function supportsTags(): bool
    {
        return method_exists(Cache::getStore(), 'tags');
    }

    protected static function pack($value)
    {
        if ($value instanceof JsonResponse) {
            return [
                'type' => 'json',
                'status' => $value->getStatusCode(),
                'data' => json_decode($value->getContent(), true),
                'headers' => $value->headers->allPreserveCase(),
            ];
        }

        return $value;
    }

    protected static function unpack($payload)
    {
        if (is_array($payload) && ($payload['type'] ?? null) === 'json') {
            $response = response()->json($payload['data'], $payload['status']);
            foreach ($payload['headers'] ?? [] as $name => $values) {
                foreach ($values as $value) {
                    $response->headers->set($name, $value, false);
                }
            }
            return $response;
        }

        return $payload;
    }
}
