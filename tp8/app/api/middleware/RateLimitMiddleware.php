<?php
declare(strict_types=1);

namespace app\api\middleware;

use think\middleware\Handle;
use think\Request;

class RateLimitMiddleware extends Handle
{
    const DEFAULT_LIMIT = 60;
    const DEFAULT_WINDOW = 60;

    private int $limit;
    private int $window;

    public function __construct()
    {
        $this->limit = env('RATE_LIMIT', self::DEFAULT_LIMIT);
        $this->window = env('RATE_WINDOW', self::DEFAULT_WINDOW);
    }

    public function handle(Request $request, \Closure $next)
    {
        $ip = $request->ip();
        $key = 'rate_limit:' . $ip;
        
        $count = (int)cache($key) ?: 0;
        
        if ($count >= $this->limit) {
            return json([
                'code' => 429,
                'msg' => '请求过于频繁，请稍后再试',
                'data' => [
                    'limit' => $this->limit,
                    'remaining' => 0,
                    'reset' => time() + $this->window
                ]
            ]);
        }

        cache($key, $count + 1, $this->window);

        $response = $next($request);
        
        if (method_exists($response, 'header')) {
            $response->header([
                'X-RateLimit-Limit' => $this->limit,
                'X-RateLimit-Remaining' => $this->limit - $count - 1,
                'X-RateLimit-Reset' => time() + $this->window
            ]);
        }
        
        return $response;
    }
}