<?php
declare(strict_types=1);

namespace app\api\controller;

use app\service\AuthService;

class Login extends ApiController
{
    protected AuthService $service;

    public function __construct()
    {
        $this->service = new AuthService();
    }

    public function login()
    {
        $data = $this->post();
        $result = $this->service->login($data);
        return $result['code'] === 200 ? $this->success($result['data'], $result['msg']) : $this->error($result['msg']);
    }

    public function logout()
    {
        $result = $this->service->logout();
        return $this->success($result['data'], $result['msg']);
    }

    public function info()
    {
        $result = $this->service->getInfo($this->adminId);
        return $result['code'] === 200 ? $this->success($result['data']) : $this->error($result['msg']);
    }
}