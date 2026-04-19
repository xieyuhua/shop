<?php

declare(strict_types=1);

namespace app\api\middleware;

class CorsMiddleware
{
    public function handle($request, \Closure $next)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, Token, Admin-Token');
        header('Access-Control-Max-Age: 1728000');
        
        if ($request->isOptions()) {
            return response('', 204);
        }

        return $next($request);
    }
}
