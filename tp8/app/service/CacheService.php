<?php
declare(strict_types=1);

namespace app\service;

use think\Cache as ThinkCache;

class CacheService
{
    const TTL = 3600;
    const PREFIX = 'mall:';

    private static function key(string $key): string
    {
        return self::PREFIX . $key;
    }

    public static function get(string $key, $default = null)
    {
        $value = ThinkCache::get(self::key($key));
        return $value ?? $default;
    }

    public static function set(string $key, $value, int $ttl = self::TTL): bool
    {
        return ThinkCache::set(self::key($key), $value, $ttl);
    }

    public static function has(string $key): bool
    {
        return ThinkCache::has(self::key($key));
    }

    public static function inc(string $key, int $step = 1)
    {
        return ThinkCache::inc(self::key($key), $step);
    }

    public static function dec(string $key, int $step = 1)
    {
        return ThinkCache::dec(self::key($key), $step);
    }

    public static function pull(string $key, $default = null)
    {
        $value = self::get($key, $default);
        self::forget($key);
        return $value;
    }

    public static function forget(string $key): bool
    {
        return ThinkCache::delete(self::key($key));
    }

    public static function clear(string $tag = ''): bool
    {
        if ($tag) {
            return ThinkCache::tag(self::PREFIX . $tag)->clear();
        }
        return ThinkCache::clear();
    }

    public static function remember(string $key, callable $callback, int $ttl = self::TTL)
    {
        $value = self::get($key);
        
        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        self::set($key, $value, $ttl);
        
        return $value;
    }

    public static function tags(array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = self::get($key);
        }
        return $result;
    }

    public static function rememberForever(string $key, callable $callback)
    {
        $value = self::get($key);
        
        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        self::set($key, $value, 0);
        
        return $value;
    }
}