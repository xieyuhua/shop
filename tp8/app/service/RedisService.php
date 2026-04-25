<?php
declare(strict_types=1);

namespace app\service;

use think\facade\Cache;

class RedisService
{
    private static function getClient()
    {
        $config = config('cache.redis');
        
        if (empty($config['host'])) {
            throw new \Exception('Redis配置未设置');
        }

        try {
            $redis = new \Redis();
            $redis->connect($config['host'], $config['port'] ?? 6379);
            
            if (!empty($config['password'])) {
                $redis->auth($config['password']);
            }
            
            if (!empty($config['select'])) {
                $redis->select($config['select']);
            }
            
            return $redis;
        } catch (\Exception $e) {
            throw new \Exception('Redis连接失败: ' . $e->getMessage());
        }
    }

    public static function set(string $key, $value, int $ttl = 0): bool
    {
        try {
            $redis = self::getClient();
            $value = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
            $ttl = $ttl ?: 86400;
            return $redis->setex($key, $ttl, $value);
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function get(string $key, $default = null)
    {
        try {
            $redis = self::getClient();
            $value = $redis->get($key);
            
            if ($value === false) {
                return $default;
            }
            
            $data = json_decode($value, true);
            return json_last_error() === JSON_ERROR_NONE ? $data : $value;
        } catch (\Exception $e) {
            return $default;
        }
    }

    public static function del(string $key): int
    {
        try {
            $redis = self::getClient();
            return $redis->del($key);
        } catch (\Exception $e) {
            return 0;
        }
    }

    public static function inc(string $key, int $step = 1): int
    {
        try {
            $redis = self::getClient();
            return $step > 0 ? $redis->incrBy($key, $step) : $redis->decrBy($key, abs($step));
        } catch (\Exception $e) {
            return 0;
        }
    }

    public static function exists(string $key): bool
    {
        try {
            $redis = self::getClient();
            return (bool)$redis->exists($key);
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function zAdd(string $key, array $members): int
    {
        try {
            $redis = self::getClient();
            $count = 0;
            $time = time();
            foreach ($members as $member => $score) {
                $count += $redis->zAdd($key, ['score' => $score ?: $time, 'member' => $member]);
            }
            return $count;
        } catch (\Exception $e) {
            return 0;
        }
    }

    public static function zRange(string $key, int $start = 0, int $end = -1): array
    {
        try {
            $redis = self::getClient();
            return $redis->zRange($key, $start, $end);
        } catch (\Exception $e) {
            return [];
        }
    }

    public static function hSet(string $key, string $field, $value): bool
    {
        try {
            $redis = self::getClient();
            $value = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
            return $redis->hSet($key, $field, $value);
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function hGet(string $key, string $field, $default = null)
    {
        try {
            $redis = self::getClient();
            $value = $redis->hGet($key, $field);
            return $value ?: $default;
        } catch (\Exception $e) {
            return $default;
        }
    }

    public static function hGetAll(string $key): array
    {
        try {
            $redis = self::getClient();
            $data = $redis->hGetAll($key);
            foreach ($data as $k => $v) {
                $data[$k] = json_decode($v, true) ?: $v;
            }
            return $data;
        } catch (\Exception $e) {
            return [];
        }
    }

    public static function lPush(string $key, ...$values): int
    {
        try {
            $redis = self::getClient();
            return $redis->lPush($key, ...$values);
        } catch (\Exception $e) {
            return 0;
        }
    }

    public static function lRange(string $key, int $start = 0, int $end = -1): array
    {
        try {
            $redis = self::getClient();
            return $redis->lRange($key, $start, $end);
        } catch (\Exception $e) {
            return [];
        }
    }
}