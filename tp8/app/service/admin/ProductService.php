<?php
declare(strict_types=1);

namespace app\service\admin;

use app\entity\ProductEntity;
use think\Paginator;

class ProductService extends \app\service\Service
{
    protected $model = 'admin.Product';

    public function getList(int $page = 1, int $limit = 15, array $filter = []): Paginator
    {
        $query = ProductEntity::order('id', 'desc');
        
        if (!empty($filter['category_id'])) {
            $query->where('category_id', $filter['category_id']);
        }
        if (!empty($filter['keyword'])) {
            $query->where('name|slug', 'like', "%{$filter['keyword']}%");
        }
        if (isset($filter['status']) && $filter['status'] !== '') {
            $query->where('status', $filter['status']);
        }
        
        return $query->paginate([
            'page' => $page,
            'list_rows' => $limit,
        ]);
    }

    public function create(array $data): array
    {
        if (empty($data['name']) || empty($data['category_id'])) {
            return $this->error('商品名称和分类不能为空');
        }
        
        $data['slug'] = $data['slug'] ?: str_slug($data['name']);
        
        $product = new ProductEntity();
        $product->save($data);
        
        return $this->result($product, '添加成功');
    }

    public function update(int $id, array $data): array
    {
        $product = ProductEntity::find($id);
        if (!$product) {
            return $this->error('记录不存在');
        }
        
        if (empty($data['slug'])) {
            $data['slug'] = str_slug($data['name']);
        }
        
        $product->save($data);
        
        return $this->result($product, '更新成功');
    }

    public function delete(int $id): array
    {
        $product = ProductEntity::find($id);
        if (!$product) {
            return $this->error('记录不存在');
        }
        
        $product->delete();
        
        return $this->result(null, '删除成功');
    }

    public function setStatus(int $id, int $status): array
    {
        $product = ProductEntity::find($id);
        if (!$product) {
            return $this->error('记录不存在');
        }
        
        $product->save(['status' => $status]);
        
        return $this->result(null, '操作成功');
    }

    public function setRecommend(int $id, int $isRecommend): array
    {
        $product = ProductEntity::find($id);
        if (!$product) {
            return $this->error('记录不存在');
        }
        
        $product->save(['is_recommend' => $isRecommend]);
        
        return $this->result(null, '操作成功');
    }

    public function getInfo(int $id): ProductEntity|null
    {
        return ProductEntity::find($id);
    }

    public function getOnSaleList(int $limit = 10): array
    {
        return ProductEntity::where('status', 1)
            ->where('stock', '>', 0)
            ->order('sales', 'desc')
            ->limit($limit)
            ->select()
            ->toArray();
    }

    public function getRecommendList(int $limit = 10): array
    {
        return ProductEntity::where('status', 1)
            ->where('is_recommend', 1)
            ->order('id', 'desc')
            ->limit($limit)
            ->select()
            ->toArray();
    }

    public function updateStock(int $id, int $quantity): array
    {
        $product = ProductEntity::find($id);
        if (!$product) {
            return $this->error('商品不存在');
        }
        
        $product->stock -= $quantity;
        $product->sales += $quantity;
        $product->save();
        
        return $this->result(null, '库存更新成功');
    }
}