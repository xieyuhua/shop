<?php

declare(strict_types=1);

namespace app\api\middleware;

class AuthMiddleware
{
    public function handle($request, \Closure $next)
    {
        $token = $request->header('Authorization', '');
        
        if (empty($token)) {
            return json(['code' => 401, 'msg' => '请先登录'])->respond();
        }
        
        $auth = \app\common\library\Token::get($token);
        if (!$auth) {
            return json(['code' => 401, 'msg' => '登录已过期'])->respond();
        }
        
        $request->userId = $auth['user_id'];
        $request->shopId = $auth['shop_id'] ?? 0;
        
        return $next($request);
    }
}
