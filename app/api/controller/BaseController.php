<?php

declare(strict_types=1);

namespace app\common\controller;

use think\App;
use app\common\library\JwtAuth;

/**
 * 控制器基类 - 仅处理参数解析和响应，不处理业务逻辑
 */
abstract class BaseController
{
    protected App $app;
    protected $request;

    protected int $userId = 0;
    protected int $shopId = 0;
    protected $userInfo = null;

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->request = $this->app->request;
    }

    /**
     * 验证用户登录 - JWT认证
     */
    protected function auth(): bool
    {
        $token = $this->getToken();

        if (empty($token)) {
            $this->error('请先登录', 401);
            return false;
        }

        $payload = JwtAuth::verify($token);
        if (!$payload || ($payload['type'] ?? '') !== 'user') {
            $this->error('登录已过期', 401);
            return false;
        }

        $this->userId = $payload['user_id'] ?? 0;
        $this->shopId = $payload['shop_id'] ?? 0;

        if ($this->userId <= 0) {
            $this->error('用户不存在', 401);
            return false;
        }

        return true;
    }

    /**
     * 验证商家登录
     */
    protected function shopAuth(): bool
    {
        if (!$this->auth()) {
            return false;
        }

        if ($this->shopId <= 0) {
            $this->error('请先入驻商家', 403);
            return false;
        }

        return true;
    }

    /**
     * 获取 Token
     */
    protected function getToken(): string
    {
        $token = $this->request->header('Authorization', '');
        if ($token && strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }

        if (empty($token)) {
            $token = $this->request->param('token', '');
        }

        return $token;
    }

    /**
     * 返回 JSON 响应
     */
    protected function json(int $code = 0, string $msg = '', $data = null): \think\Response
    {
        $result = [
            'code' => $code,
            'msg' => $msg,
            'time' => time(),
        ];

        if ($data !== null) {
            $result['data'] = $data;
        }

        return json($result);
    }

    /**
     * 返回成功响应
     */
    protected function success($data = null, string $msg = '操作成功'): \think\Response
    {
        return $this->json(0, $msg, $data);
    }

    /**
     * 返回错误响应
     */
    protected function error(string $msg = '操作失败', int $code = 1): \think\Response
    {
        return $this->json($code, $msg);
    }
}
