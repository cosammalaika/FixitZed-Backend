<?php

return [
    'api_cache_enabled' => env('API_CACHE_ENABLED', false),
    'api_cache_ttl' => (int) env('API_CACHE_TTL', 300),
];
