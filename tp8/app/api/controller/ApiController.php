<?php
declare(strict_types=1);

namespace app\api\controller;

use think\App;
use think\exception\ValidateException;

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

    protected function validate(array $data, string $validate, string $scene = ''): bool
    {
        try {
            $v = new $validate();
            if ($scene) {
                $v->scene($scene);
            }
            
            if (!$v->check($data)) {
                return $this->error($v->getError());
            }
            return true;
        } catch (ValidateException $e) {
            return $this->error($e->getMessage());
        }
    }

    protected function success($data = null, $msg = 'success'): \think\Response
    {
        return json(['code' => 200, 'msg' => $msg, 'data' => $data]);
    }

    protected function error($msg = 'error', $code = 400): \think\Response
    {
        return json(['code' => $code, 'msg' => $msg]);
    }
}