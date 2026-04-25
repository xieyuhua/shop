<?php
declare(strict_types=1);

namespace app\api\middleware;

use think\middleware\Handle;

class AuthMiddleware extends Handle
{
    public function handle($request, \Closure $next)
    {
        $token = $request->header('Authorization');
        
        if (!$token) {
            return json(['code' => 401, 'msg' => '未授权']);
        }
        
        $token = str_replace('Bearer ', '', $token);
        
        try {
            $secret = env('JWT_SECRET', 'mall_jwt_secret_key_2024');
            $payload = \firebase\jwt\JWT::decode($token, $secret);
            
            $request->adminId = $payload['sub'] ?? 0;
            $request->adminInfo = $payload['data'] ?? [];
            
        } catch (\Exception $e) {
            return json(['code' => 401, 'msg' => 'Token无效']);
        }
        
        return $next($request);
    }
}