<?php
declare(strict_types=1);

namespace app\api\controller;

use app\model\admin\ConfigModel;

class Config extends ApiController
{
    public function index()
    {
        $group = $this->request->param('group', 'basic');
        $list = ConfigModel::where('group', $group)->select();
        
        return $this->success($list);
    }

    public function save()
    {
        $data = $this->request->post();
        
        foreach ($data as $name => $value) {
            ConfigModel::setValue($name, $value);
        }
        
        return $this->success(null, '保存成功');
    }
}