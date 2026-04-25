<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\BaseController;

abstract class AdminBase extends BaseController
{
    protected $adminInfo = null;

    protected function initialize()
    {
        parent::initialize();
        $this->adminInfo = session('admin');
        if (!$this->adminInfo) {
            if ($this->request->isAjax()) {
                return json(['code' => 401, 'msg' => '请先登录']);
            }
            return redirect('/admin/login');
        }
        $this->assign('adminInfo', $this->adminInfo);
    }

    protected function success($msg = '操作成功', $data = null)
    {
        return json(['code' => 200, 'msg' => $msg, 'data' => $data]);
    }

    protected function error($msg = '操作失败')
    {
        return json(['code' => 400, 'msg' => $msg]);
    }

    protected function checkAdmin(): bool
    {
        return !empty($this->adminInfo);
    }
}