<?php
declare(strict_types=1);

namespace app\api\controller;

use app\model\admin\AdminModel;
use think\exception\ValidateException;

class Login extends ApiController
{
    public function login()
    {
        $username = $this->request->param('username');
        $password = $this->request->param('password');

        if (!$username || !$password) {
            return $this->error('用户名和密码不能为空');
        }

        $admin = AdminModel::where('username', $username)->find();
        
        if (!$admin) {
            return $this->error('用户不存在');
        }

        if (!$admin->isActive()) {
            return $this->error('账户已被禁用');
        }

        if (!$admin->checkPassword($password)) {
            return $this->error('密码错误');
        }

        $admin->login_ip = $this->request->ip();
        $admin->login_time = date('Y-m-d H:i:s');
        $admin->save();

        $secret = env('JWT_SECRET', 'mall_jwt_secret_key_2024');
        $expire = env('JWT_EXPIRE', 7200);

        $payload = [
            'sub' => $admin->id,
            'data' => $admin->toSafe(),
            'iat' => time(),
            'exp' => time() + $expire,
        ];

        $token = \firebase\jwt\JWT::encode($payload, $secret);

        return $this->success([
            'token' => $token,
            'user' => $admin->toSafe(),
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