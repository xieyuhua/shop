<?php
declare(strict_types=1);

namespace app\service;

use app\model\admin\ProductModel;
use app\model\admin\ProductModel as Model;

class ProductService extends Service
{
    protected string $model = 'product';

    public function list(array $params = []): array
    {
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 15;
        $category_id = $params['category_id'] ?? 0;
        $keyword = $params['keyword'] ?? '';
        $status = $params['status'] ?? '';

        $query = db('product')->order('id', 'desc');

        if ($category_id > 0) {
            $query->where('category_id', $category_id);
        }
        if ($keyword) {
            $query->where('name|slug', 'like', "%{$keyword}%");
        }
        if ($status !== '') {
            $query->where('status', $status);
        }

        $result = $this->paginate($query, $page, $limit);
        $result['categories'] = db('category')->where('status', 1)->order('sort', 'asc')->select()->toArray();

        return $result;
    }

    public function create(array $data): array
    {
        if (empty($data['name']) || empty($data['category_id'])) {
            return $this->error('商品名称和分类不能为空');
        }

        $data['slug'] = $data['slug'] ?? str_slug($data['name']);

        $product = new ProductModel();
        $product->save($data);

        return $this->success(['id' => $product->id], '添加成功');
    }

    public function update(int $id, array $data): array
    {
        try {
            $product = $this->findOrFail($id);
        } catch (\RuntimeException) {
            return $this->error('商品不存在');
        }

        if (empty($data['slug'])) {
            $data['slug'] = str_slug($data['name']);
        }
        unset($data['id']);

        $product->save($data);

        return $this->success(null, '更新成功');
    }

    public function delete(int $id): array
    {
        if (!$this->find($id)) {
            return $this->error('商品不存在');
        }

        $this->delete($id);

        return $this->success(null, '删除成功');
    }

    public function getOptions(): array
    {
        return db('product')->where('status', 1)->field('id, name, image, price, stock')->select()->toArray();
    }
}