<?php
declare(strict_types=1);

namespace app\api\controller;

use think\facade\Db;

class Config extends ApiController
{
    public function index()
    {
        $group = $this->request->param('group', 'basic');
        $list = Db::name('config')->where('group', $group)->select();
        
        return $this->success($list);
    }

    public function save()
    {
        $data = $this->request->post();
        
        foreach ($data as $name => $value) {
            Db::name('config')->where('name', $name)->update(['value' => $value]);
        }
        
        return $this->success(null, '保存成功');
    }
}