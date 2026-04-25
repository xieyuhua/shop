<?php
declare(strict_types=1);

namespace app\api\controller;

use app\model\admin\ProductModel;
use app\model\admin\CategoryModel;

class Product extends ApiController
{
    public function index()
    {
        $page = $this->request->param('page', 1);
        $limit = $this->request->param('limit', 15);
        $category_id = $this->request->param('category_id', 0);
        $keyword = $this->request->param('keyword', '');
        $status = $this->request->param('status', '');

        $query = ProductModel::order('id', 'desc');
        
        if ($category_id > 0) {
            $query->where('category_id', $category_id);
        }
        if ($keyword) {
            $query->where('name|slug', 'like', "%{$keyword}%");
        }
        if ($status !== '') {
            $query->where('status', $status);
        }

        $list = $query->page($page, $limit)->select();
        $total = $query->count();

        $categories = CategoryModel::where('status', 1)->order('sort', 'asc')->select();

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

        $data['slug'] = $data['slug'] ?? str_slug($data['name']);

        $product = new ProductModel();
        $product->save($data);
        
        return $this->success(['id' => $product->id], '添加成功');
    }

    public function update()
    {
        $id = $this->request->post('id');
        $data = $this->request->post();

        if (empty($data['slug'])) {
            $data['slug'] = str_slug($data['name']);
        }
        unset($data['id']);

        $product = ProductModel::find($id);
        if (!$product) {
            return $this->error('商品不存在');
        }

        $product->save($data);
        
        return $this->success(null, '更新成功');
    }

    public function delete()
    {
        $id = $this->request->param('id');
        
        if (!$id) {
            return $this->error('参数错误');
        }
        
        $product = ProductModel::find($id);
        if (!$product) {
            return $this->error('商品不存在');
        }

        $product->delete();
        
        return $this->success(null, '删除成功');
    }
}