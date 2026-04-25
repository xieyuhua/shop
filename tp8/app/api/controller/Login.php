<?php
declare(strict_types=1);

namespace app\api\controller;

use think\facade\Db;
use think\Model;

class Login extends ApiController
{
    public function login()
    {
        $username = $this->request->param('username');
        $password = $this->request->param('password');

        if (!$username || !$password) {
            return $this->error('用户名和密码不能为空');
        }

        $admin = Db::name('admin')->where('username', $username)->find();
        
        if (!$admin) {
            return $this->error('用户不存在');
        }

        if ($admin['status'] == 0) {
            return $this->error('账户已被禁用');
        }

        if (!password_verify($password, $admin['password'])) {
            return $this->error('密码错误');
        }

        Db::name('admin')->where('id', $admin['id'])->update([
            'login_ip' => $this->request->ip(),
            'login_time' => date('Y-m-d H:i:s'),
        ]);

        $secret = env('JWT_SECRET', 'mall_jwt_secret_key_2024');
        $expire = env('JWT_EXPIRE', 7200);

        $payload = [
            'sub' => $admin['id'],
            'data' => [
                'id' => $admin['id'],
                'username' => $admin['username'],
                'nickname' => $admin['nickname'],
            ],
            'iat' => time(),
            'exp' => time() + $expire,
        ];

        $token = \firebase\jwt\JWT::encode($payload, $secret);

        return $this->success([
            'token' => $token,
            'user' => $payload['data'],
            'expire' => $expire,
        ]);
    }

    public function logout()
    {
        return $this->success(null, '已退出登录');
    }

    public function info()
    {
        return $this->success($this->adminInfo);
    }
}