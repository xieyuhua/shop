<?php
declare(strict_types=1);

namespace app\api\controller;

use app\service\NotifyService;

class Notify extends ApiController
{
    public function index()
    {
        $page = $this->request->param('page', 1);
        $limit = $this->request->param('limit', 15);
        
        $result = NotifyService::getList($this->adminId, $page, $limit);
        
        return $this->success($result);
    }

    public function unread()
    {
        $count = NotifyService::getUnreadCount($this->adminId);
        
        return $this->success(['count' => $count]);
    }

    public function read()
    {
        $id = $this->request->param('id');
        
        if ($id) {
            NotifyService::read($id);
        } else {
            NotifyService::readAll($this->adminId);
        }
        
        return $this->success(null, '已标记为已读');
    }

    public function delete()
    {
        $id = $this->request->param('id');
        
        $result = NotifyService::delete($id);
        
        return $result ? $this->success(null, '删除成功') : $this->error('删除失败');
    }
}