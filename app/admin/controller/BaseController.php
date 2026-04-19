<?php

declare(strict_types=1);

namespace app\admin\controller;

use think\App;
use app\common\library\JwtAuth;

/**
 * 管理后台控制器基类 - 仅处理参数解析和响应
 */
abstract class BaseController
{
    protected App $app;
    protected $request;

    protected int $adminId = 0;
    protected int $roleId = 0;
    protected $adminInfo = null;

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->request = $this->app->request;
    }

    /**
     * 验证管理员登录 - JWT认证
     */
    protected function adminAuth(): bool
    {
        $token = $this->getAdminToken();

        if (empty($token)) {
            $this->error('请先登录', 401);
            return false;
        }

        $payload = JwtAuth::verify($token);
        if (!$payload || ($payload['type'] ?? '') !== 'admin') {
            $this->error('登录已过期', 401);
            return false;
        }

        $this->adminId = $payload['admin_id'] ?? 0;
        $this->roleId = $payload['role_id'] ?? 0;

        if ($this->adminId <= 0) {
            $this->error('管理员不存在', 401);
            return false;
        }

        return true;
    }

    /**
     * 获取管理员 Token
     */
    protected function getAdminToken(): string
    {
        $token = $this->request->header('Admin-Token', '');
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
