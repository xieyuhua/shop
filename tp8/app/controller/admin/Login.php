<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\service\admin\AdminService;

class Login extends AdminBase
{
    protected $noNeedLogin = ['index', 'login'];

    protected AdminService $service;

    public function __construct()
    {
        $this->service = new AdminService();
    }

    public function index()
    {
        return view('', ['title' => '后台登录']);
    }

    public function login()
    {
        $username = $this->request->param('username');
        $password = $this->request->param('password');

        if (!$username || !$password) {
            return $this->error('用户名和密码不能为空');
        }

        $result = $this->service->login($username, $password);
        
        if ($result['code'] !== 200) {
            return $this->error($result['msg']);
        }

        return $this->success('登录成功');
    }

    public function logout()
    {
        $result = $this->service->logout();
        return $this->success($result['msg']);
    }
}