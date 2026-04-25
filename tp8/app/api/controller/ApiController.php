<?php
declare(strict_types=1);

namespace app\api\controller;

use think\annotation\route\Group;
use think\annotation\route\Route;
use think\App;

#[Group('api')]
abstract class ApiController
{
    protected $request;
    protected $app;
    protected $adminId = 0;
    protected $adminInfo = [];

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->request = $this->app->request;
        $this->adminId = $this->request->adminId ?? 0;
        $this->adminInfo = $this->request->adminInfo ?? [];
    }

    protected function success($data = null, $msg = 'success')
    {
        return json(['code' => 200, 'msg' => $msg, 'data' => $data]);
    }

    protected function error($msg = 'error', $code = 400)
    {
        return json(['code' => $code, 'msg' => $msg]);
    }
}