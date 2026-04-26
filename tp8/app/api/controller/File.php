<?php
declare(strict_types=1);

namespace app\api\controller;

use app\service\FileService;

class File extends ApiController
{
    protected FileService $service;

    public function __construct()
    {
        $this->service = new FileService();
    }

    public function upload()
    {
        $group = $this->param('group', 'default');
        $result = $this->service->upload($group);
        return $result['code'] === 200 ? $this->success($result['data'], $result['msg']) : $this->error($result['msg']);
    }

    public function index()
    {
        $params = $this->param();
        $result = $this->service->list($params);
        return $this->success($result);
    }

    public function delete()
    {
        $id = (int) $this->param('id');
        $result = $this->service->delete($id);
        return $result['code'] === 200 ? $this->success($result['data'], $result['msg']) : $this->error($result['msg']);
    }
}