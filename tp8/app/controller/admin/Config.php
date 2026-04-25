<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\service\admin\ConfigService;

class Config extends AdminBase
{
    protected ConfigService $service;

    public function __construct()
    {
        $this->service = new ConfigService();
    }

    public function index()
    {
        $group = $this->request->param('group', 'basic');
        $list = $this->service->getList($group);
        
        return view('', ['title' => '系统配置', 'list' => $list, 'group' => $group]);
    }

    public function save()
    {
        $data = $this->request->post();
        $result = $this->service->saveBatch($data);
        
        if ($result['code'] !== 200) {
            return $this->error($result['msg']);
        }
        
        return $this->success('保存成功');
    }
}