<?php
declare(strict_types=1);

namespace app\api\middleware;

use think\middleware\Handle;
use think\Request;

class SecurityMiddleware extends Handle
{
    private array $allowedIp = [];
    private array $blockedIp = [];
    private bool $enableCORS = true;

    public function handle(Request $request, \Closure $next)
    {
        $ip = $request->ip();

        if ($this->isBlocked($ip)) {
            return json(['code' => 403, 'msg' => 'IP被禁止']);
        }

        if (!$this->isAllowed($ip)) {
            return json(['code' => 403, 'msg' => '不在允许IP范围内']);
        }

        if ($this->isSecurityCheck($request)) {
            return $this->securityCheckFailed($request);
        }

        $response = $next($request);

        if ($this->enableCORS && $response) {
            $response->header([
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With',
                'Access-Control-Max-Age' => '3600'
            ]);
        }

        if ($request->isOptions()) {
            return json(['code' => 200])->code(204);
        }

        return $response;
    }

    private function isBlocked(string $ip): bool
    {
        if (in_array($ip, $this->blockedIp)) {
            return true;
        }

        $blockList = Cache('security:block') ?: [];
        return in_array($ip, $blockList);
    }

    private function isAllowed(string $ip): bool
    {
        if (empty($this->allowedIp)) {
            return true;
        }

        foreach ($this->allowedIp as $allow) {
            if ($this->ipMatch($ip, $allow)) {
                return true;
            }
        }

        return false;
    }

    private function ipMatch(string $ip, string $pattern): bool
    {
        if ($pattern === '*') {
            return true;
        }

        if (strpos($pattern, '*') !== false) {
            $pattern = str_replace(['.', '*'], ['\.', '.*'], $pattern);
            return (bool)preg_match("/^{$pattern$/", $ip);
        }

        return $ip === $pattern;
    }

    private function isSecurityCheck(Request $request): bool
    {
        $sqlKeywords = ['union', 'select', 'insert', 'update', 'delete', 'drop', 'exec', 'execute', 'script', '<script'];
        $params = array_merge($request->get(), $request->post());
        
        foreach ($params as $value) {
            if (is_string($value)) {
                $value = strtolower($value);
                foreach ($sqlKeywords as $keyword) {
                    if (strpos($value, $keyword) !== false) {
                        $this->logSecurity($request, 'SQL注入', $value);
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function securityCheckFailed(Request $request): bool
    {
        return json(['code' => 403, 'msg' => '安全验证失败']);
    }

    private function logSecurity(Request $request, string $type, string $content): void
    {
        \app\service\LogService::error("安全拦截: {$type}", [
            'ip' => $request->ip(),
            'url' => $request->url(true),
            'content' => $content
        ]);
    }
}