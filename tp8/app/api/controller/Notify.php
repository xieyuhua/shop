<?php
declare(strict_types=1);

namespace app\api\controller;

use app\service\NotifyService;

class Notify extends ApiController
{
    protected NotifyService $service;

    public function __construct()
    {
        $this->service = new NotifyService();
    }

    public function index()
    {
        $page = (int) $this->param('page', 1);
        $limit = (int) $this->param('limit', 15);
        $result = $this->service->getList($this->adminId, $page, $limit);
        return $this->success($result);
    }

    public function unread()
    {
        $count = $this->service->getUnreadCount($this->adminId);
        return $this->success(['count' => $count]);
    }

    public function read()
    {
        $id = (int) $this->param('id');
        if ($id) {
            $this->service->read($id);
        } else {
            $this->service->readAll($this->adminId);
        }
        return $this->success(null, '已标记为已读');
    }

    public function delete()
    {
        $id = (int) $this->param('id');
        $result = $this->service->delete($id);
        return $result ? $this->success(null, '删除成功') : $this->error('删除失败');
    }
}