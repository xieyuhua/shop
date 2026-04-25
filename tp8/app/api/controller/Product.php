<?php
declare(strict_types=1);

namespace app\api\controller;

use think\facade\Db;

class Product extends ApiController
{
    public function index()
    {
        $page = $this->request->param('page', 1);
        $limit = $this->request->param('limit', 15);
        $category_id = $this->request->param('category_id', 0);
        $keyword = $this->request->param('keyword', '');
        $status = $this->request->param('status', '');

        $where = [];
        if ($category_id > 0) {
            $where[] = ['category_id', '=', $category_id];
        }
        if ($keyword) {
            $where[] = ['name|slug', 'like', "%{$keyword}%"];
        }
        if ($status !== '') {
            $where[] = ['status', '=', $status];
        }

        $list = Db::name('product')
            ->where($where)
            ->order('id', 'desc')
            ->page($page, $limit)
            ->select();

        $total = Db::name('product')->where($where)->count();

        $categories = Db::name('category')->where('status', 1)->order('sort', 'asc')->select();

        return $this->success([
            'list' => $list,
            'total' => $total,
            'categories' => $categories,
        ]);
    }

    public function save()
    {
        $data = $this->request->post();
        
        if (empty($data['name']) || empty($data['category_id'])) {
            return $this->error('商品名称和分类不能为空');
        }

        $data['slug'] = $data['slug'] ?: str_slug($data['name']);
        $data['create_time'] = date('Y-m-d H:i:s');

        $id = Db::name('product')->insertGetId($data);
        
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

        $result = Db::name('product')->where('id', $id)->update($data);
        
        return $result !== false ? $this->success(null, '更新成功') : $this->error('更新失败');
    }

    public function delete()
    {
        $id = $this->request->param('id');
        
        if (!$id) {
            return $this->error('参数错误');
        }
        
        $result = Db::name('product')->delete($id);
        
        return $result ? $this->success(null, '删除成功') : $this->error('删除失败');
    }
}