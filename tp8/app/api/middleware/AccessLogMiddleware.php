<?php
declare(strict_types=1);

namespace app\api\middleware;

use think\middleware\Handle;
use think\Request;

class AccessLogMiddleware extends Handle
{
    private bool $enable = true;
    private int $sampleRate = 100;

    public function handle(Request $request, \Closure $next)
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        $response = $next($request);

        if ($this->enable && $this->shouldLog()) {
            $this->log($request, $response, $startTime, $startMemory);
        }

        return $response;
    }

    private function shouldLog(): bool
    {
        return rand(1, $this->sampleRate) === 1;
    }

    private function log(Request $request, $response, float $startTime, int $startMemory): void
    {
        $costTime = round((microtime(true) - $startTime) * 1000, 2);
        $memory = round((memory_get_usage() - $startMemory) / 1024, 2);

        $data = [
            'method' => $request->method(),
            'url' => $request->url(true),
            'ip' => $request->ip(),
            'code' => $response->getCode(),
            'cost_time' => $costTime . 'ms',
            'memory' => $memory . 'KB',
            'user_agent' => $request->header('user-agent'),
            'param' => json_encode(array_merge($request->get(), $request->post()), JSON_UNESCAPED_UNICODE)
        ];

        $level = $costTime > 1000 ? 'warning' : 'info';
        
        log($level, json_encode($data, JSON_UNESCAPED_UNICODE));
    }
}