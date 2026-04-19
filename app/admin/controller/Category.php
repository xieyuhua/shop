<?php

declare(strict_types=1);

namespace app\admin\controller;

use app\common\controller\BaseController;
use app\common\model\Category;

class CategoryController extends BaseController
{
    public function list()
    {
        $this->adminAuth();

        $list = Category::order('sort', 'asc')->select();

        return $this->success($list);
    }

    public function tree()
    {
        $this->adminAuth();

        $list = Category::getTree();

        return $this->success($list);
    }

    public function options()
    {
        $this->adminAuth();

        $list = Category::getOptions();

        return $this->success($list);
    }

    public function detail()
    {
        $this->adminAuth();
        $id = $this->request->get('id');

        $category = Category::find($id);
        if (!$category) {
            return $this->error('分类不存在');
        }

        return $this->success($category);
    }

    public function create()
    {
        $this->adminAuth();
        $data = $this->request->post();

        if (empty($data['name'])) {
            return $this->error('请输入分类名称');
        }

        $category = new Category();
        $category->pid = $data['pid'] ?? 0;
        $category->name = $data['name'];
        $category->icon = $data['icon'] ?? '';
        $category->image = $data['image'] ?? '';
        $category->sort = $data['sort'] ?? 100;
        $category->is_show = $data['is_show'] ?? 1;
        $category->is_nav = $data['is_nav'] ?? 0;
        $category->save();

        return $this->success($category);
    }

    public function update()
    {
        $this->adminAuth();
        $data = $this->request->post();

        $category = Category::find($data['id']);
        if (!$category) {
            return $this->error('分类不存在');
        }

        $category->name = $data['name'] ?? $category->name;
        $category->pid = $data['pid'] ?? $category->pid;
        $category->icon = $data['icon'] ?? $category->icon;
        $category->image = $data['image'] ?? $category->image;
        $category->sort = $data['sort'] ?? $category->sort;
        $category->is_show = $data['is_show'] ?? $category->is_show;
        $category->is_nav = $data['is_nav'] ?? $category->is_nav;
        $category->save();

        return $this->success($category);
    }

    public function delete()
    {
        $this->adminAuth();
        $id = $this->request->post('id');

        $category = Category::find($id);
        if (!$category) {
            return $this->error('分类不存在');
        }

        $hasChildren = Category::where('pid', $id)->count();
        if ($hasChildren > 0) {
            return $this->error('请先删除子分类');
        }

        $hasProducts = \app\common\model\Product::where('category_id', $id)->count();
        if ($hasProducts > 0) {
            return $this->error('该分类下有商品，无法删除');
        }

        $category->delete();

        return $this->success();
    }
}
