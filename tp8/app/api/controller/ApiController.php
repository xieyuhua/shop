<?php
declare(strict_types=1);

namespace app\api\controller;

use think\App;

abstract class ApiController
{
    protected $request;
    protected $app;
    protected int $adminId = 0;
    protected array $adminInfo = [];

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->request = $app->request;
        $this->adminId = $this->request->adminId ?? 0;
        $this->adminInfo = $this->request->adminInfo ?? [];
    }

    protected function param(string $name = '', mixed $default = null): mixed
    {
        return $name ? ($this->request->param($name) ?? $default) : $this->request->param();
    }

    protected function post(string $name = ''): mixed
    {
        return $name ? $this->request->post($name) : $this->request->post();
    }

    protected function id(int $default = 0): int
    {
        return (int) ($this->request->param('id') ?? $default);
    }

    protected function page(int $default = 1): int
    {
        return (int) ($this->request->param('page') ?? $default);
    }

    protected function limit(int $default = 15): int
    {
        return (int) ($this->request->param('limit') ?? $default);
    }

    protected function success(mixed $data = null, string $msg = 'success'): \think\Response
    {
        return json(['code' => 200, 'msg' => $msg, 'data' => $data]);
    }

    protected function error(string $msg = 'error', int $code = 400): \think\Response
    {
        return json(['code' => $code, 'msg' => $msg, 'data' => null]);
    }

    protected function result(int $code, string $msg, mixed $data = null): \think\Response
    {
        return json(['code' => $code, 'msg' => $msg, 'data' => $data]);
    }

    protected function parse(array $result, mixed $default = null): \think\Response
    {
        return $result['code'] === 200 
            ? $this->success($result['data'] ?? $default, $result['msg']) 
            : $this->error($result['msg'], $result['code'] ?? 400);
    }

    protected function parseList(array $result): \think\Response
    {
        return $result['code'] === 200 
            ? $this->success($result['data']) 
            : $this->error($result['msg']);
    }
}