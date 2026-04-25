<?php
declare(strict_types=1);

namespace app\api\controller;

use think\facade\Db;

class Category extends ApiController
{
    public function index()
    {
        $list = Db::name('category')->order('sort', 'asc')->select();
        
        return $this->success($list);
    }

    public function save()
    {
        $data = $this->request->post();
        
        if (empty($data['name'])) {
            return $this->error('分类名称不能为空');
        }

        $data['slug'] = $data['slug'] ?: str_slug($data['name']);
        $data['create_time'] = date('Y-m-d H:i:s');

        $id = Db::name('category')->insertGetId($data);
        
        return $this->success(['id' => $id], '添加成功');
    }

    public function update()
    {
        $id = $this->request->post('id');
        $data = $this->request->post();

        if (empty($data['slug'])) {
            $data['slug'] = str_slug($data['name']);
        }
        
        if (isset($data['id'])) {
            unset($data['id']);
        }

        $result = Db::name('category')->where('id', $id)->update($data);
        
        return $result !== false ? $this->success(null, '更新成功') : $this->error('更新失败');
    }

    public function delete()
    {
        $id = $this->request->param('id');
        
        if (Db::name('category')->where('pid', $id)->count() > 0) {
            return $this->error('请先删除子分类');
        }

        if (Db::name('product')->where('category_id', $id)->count() > 0) {
            return $this->error('该分类下有商品，无法删除');
        }

        $result = Db::name('category')->delete($id);
        
        return $result ? $this->success(null, '删除成功') : $this->error('删除失败');
    }
}