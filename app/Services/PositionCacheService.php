<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class PositionCacheService
{
    private const CACHE_TTL = 3600;

    public function remember(string $date, \Closure $callback): mixed
    {
        $cacheKey = $this->generateCacheKey($date);
        return Cache::remember($cacheKey, self::CACHE_TTL, $callback);
    }

    public function has(string $date): bool
    {
        return Cache::has($this->generateCacheKey($date));
    }

    public function put(string $date, mixed $value): void
    {
        Cache::put($this->generateCacheKey($date), $value, self::CACHE_TTL);
    }

    private function generateCacheKey(string $date): string
    {
        return "apptica_positions_{$date}";
    }
}
