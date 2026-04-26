<?php
declare(strict_types=1);

namespace app\api\controller;

use app\service\ConfigService;

class Config extends ApiController
{
    protected ConfigService $service;

    public function __construct()
    {
        $this->service = new ConfigService();
    }

    public function index()
    {
        $group = $this->param('group', 'basic');
        $result = $this->service->index($group);
        return $this->success($result['data'], $result['msg']);
    }

    public function save()
    {
        $data = $this->post();
        $result = $this->service->save($data);
        return $result['code'] === 200 ? $this->success($result['data'], $result['msg']) : $this->error($result['msg']);
    }
}