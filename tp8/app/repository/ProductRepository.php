<?php
declare(strict_types=1);

namespace app\repository;

use app\entity\ProductEntity;

class ProductRepository extends Repository
{
    protected $model = 'admin.Product';

    public function findByCategory(int $categoryId): array
    {
        return ProductEntity::where('category_id', $categoryId)
            ->order('id', 'desc')
            ->select()
            ->toArray();
    }

    public function findOnSale(int $limit = 10): array
    {
        return ProductEntity::where('status', 1)
            ->where('stock', '>', 0)
            ->order('sales', 'desc')
            ->limit($limit)
            ->select()
            ->toArray();
    }

    public function findRecommend(int $limit = 10): array
    {
        return ProductEntity::where('status', 1)
            ->where('is_recommend', 1)
            ->order('id', 'desc')
            ->limit($limit)
            ->select()
            ->toArray();
    }

    public function search(string $keyword): array
    {
        return ProductEntity::where('name|slug', 'like', "%{$keyword}%")
            ->order('id', 'desc')
            ->select()
            ->toArray();
    }

    public function getLowStock(int $threshold = 10): array
    {
        return ProductEntity::where('status', 1)
            ->where('stock', '<', $threshold)
            ->order('stock', 'asc')
            ->select()
            ->toArray();
    }
}