<?php
declare(strict_types=1);

namespace app\api\controller;

use app\model\admin\CategoryModel;
use app\model\admin\ProductModel;

class Category extends ApiController
{
    public function index()
    {
        $list = CategoryModel::order('sort', 'asc')->select();
        
        return $this->success($list);
    }

    public function save()
    {
        $data = $this->request->post();
        
        if (empty($data['name'])) {
            return $this->error('分类名称不能为空');
        }

        $data['slug'] = $data['slug'] ?? str_slug($data['name']);

        $category = new CategoryModel();
        $category->save($data);
        
        return $this->success(['id' => $category->id], '添加成功');
    }

    public function update()
    {
        $id = $this->request->post('id');
        $data = $this->request->post();

        if (empty($data['slug'])) {
            $data['slug'] = str_slug($data['name']);
        }
        unset($data['id']);

        $category = CategoryModel::find($id);
        if (!$category) {
            return $this->error('分类不存在');
        }

        $category->save($data);
        
        return $this->success(null, '更新成功');
    }

    public function delete()
    {
        $id = $this->request->param('id');
        
        if (CategoryModel::where('pid', $id)->count() > 0) {
            return $this->error('请先删除子分类');
        }

        if (ProductModel::where('category_id', $id)->count() > 0) {
            return $this->error('该分类下有商品，无法删除');
        }

        $category = CategoryModel::find($id);
        if (!$category) {
            return $this->error('分类不存在');
        }

        $category->delete();
        
        return $this->success(null, '删除成功');
    }
}