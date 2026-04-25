<?php
declare(strict_types=1);

namespace app\api\middleware;

use think\middleware\Handle;
use think\Request;
use think\facade\Db;

class ApiCacheMiddleware extends Handle
{
    private int $ttl = 60;
    private array $excludeMethods = ['POST', 'PUT', 'DELETE'];
    private array $excludeUrls = [];

    public function handle(Request $request, \Closure $next)
    {
        if ($this->shouldCache($request)) {
            $cacheKey = $this->getCacheKey($request);
            $cached = cache($cacheKey);
            
            if ($cached) {
                return json($cached)->cache();
            }

            $response = $next($request);
            cache($cacheKey, $response->getContent(), $this->ttl);

            return $response;
        }

        return $next($request);
    }

    private function shouldCache(Request $request): bool
    {
        if (in_array($request->method(), $this->excludeMethods)) {
            return false;
        }

        $url = $request->url(true);
        foreach ($this->excludeUrls as $exclude) {
            if (strpos($url, $exclude) !== false) {
                return false;
            }
        }

        return true;
    }

    private function getCacheKey(Request $request): string
    {
        return 'api:cache:' . md5($request->url(true) . json_encode($request->param()));
    }
}