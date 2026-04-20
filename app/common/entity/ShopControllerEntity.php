<?php

declare(strict_types=1);

namespace app\common\entity;

use app\common\model\Shop;
use app\common\model\Product;

/**
 * 店铺控制器实体（API端）
 */
class ShopControllerEntity
{
    /**
     * 获取店铺列表
     */
    public function getList(array $params): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $limit = min(50, max(1, (int) ($params['limit'] ?? 15)));
        $categoryId = (int) ($params['category_id'] ?? 0);
        $keyword = $params['keyword'] ?? '';
        $sort = $params['sort'] ?? 'default';

        $query = Shop::where('status', Shop::STATUS_ACTIVE);

        if ($categoryId > 0) {
            $query->where('category_id', $categoryId);
        }

        if ($keyword) {
            $keyword = addcslashes($keyword, '%_');
            $query->where('shop_name', 'like', '%' . $keyword . '%');
        }

        $query = $this->applySort($query, $sort);

        $total = $query->count();
        $list = $query->page($page, $limit)->select();

        return [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit),
        ];
    }

    /**
     * 获取店铺详情
     */
    public function getDetail(int $id): ?array
    {
        $shop = Shop::with(['category'])
            ->where('id', $id)
            ->where('status', Shop::STATUS_ACTIVE)
            ->find();

        return $shop ? $shop->toArray() : null;
    }

    /**
     * 获取店铺商品列表
     */
    public function getProducts(array $params): array
    {
        $shopId = (int) ($params['shop_id'] ?? 0);
        $page = max(1, (int) ($params['page'] ?? 1));
        $limit = min(50, max(1, (int) ($params['limit'] ?? 15)));
        $sort = $params['sort'] ?? 'latest';

        $query = Product::with(['category'])
            ->where('shop_id', $shopId)
            ->where('is_on_sale', 1)
            ->where('status', Product::STATUS_PASS);

        $query = $this->applyProductSort($query, $sort);

        $total = $query->count();
        $list = $query->page($page, $limit)->select();

        return [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit),
        ];
    }

    /**
     * 申请店铺
     */
    public function apply(int $userId, array $data): array
    {
        $errors = $this->validateApply($data);
        if (!empty($errors)) {
            return ['success' => false, 'msg' => implode(', ', $errors)];
        }

        $exists = Shop::where('user_id', $userId)->find();
        if ($exists) {
            return ['success' => false, 'msg' => '您已申请过店铺'];
        }

        $shop = Shop::apply($userId, $data);

        return ['success' => true, 'data' => $shop->toArray()];
    }

    /**
     * 获取我的店铺信息
     */
    public function getMyShop(int $userId): ?array
    {
        $shop = Shop::with(['category'])
            ->where('user_id', $userId)
            ->find();

        return $shop ? $shop->toArray() : null;
    }

    /**
     * 获取店铺统计
     */
    public function getStatistics(int $shopId): array
    {
        $shop = Shop::find($shopId);
        if (!$shop) {
            return [];
        }

        return [
            'total_products' => Product::where('shop_id', $shopId)->count(),
            'total_sales' => $shop->total_sales ?? 0,
            'total_amount' => $shop->total_amount ?? 0,
            'frozen_amount' => $shop->frozen_amount ?? 0,
        ];
    }

    private function validateApply(array $data): array
    {
        $errors = [];

        if (empty($data['shop_name'])) {
            $errors[] = '请输入店铺名称';
        }
        if (empty($data['contact_name'])) {
            $errors[] = '请输入联系人';
        }
        if (empty($data['contact_mobile'])) {
            $errors[] = '请输入联系电话';
        }
        if (empty($data['category_id'])) {
            $errors[] = '请选择店铺分类';
        }

        return $errors;
    }

    private function applySort($query, string $sort)
    {
        return match ($sort) {
            'sales' => $query->order('total_sales', 'desc'),
            'new' => $query->order('create_time', 'desc'),
            'recommend' => $query->where('is_recommend', 1)->order('sort', 'asc'),
            default => $query->order('sort', 'asc'),
        };
    }

    private function applyProductSort($query, string $sort)
    {
        return match ($sort) {
            'sales' => $query->order('sales', 'desc'),
            'price_asc' => $query->order('price', 'asc'),
            'price_desc' => $query->order('price', 'desc'),
            default => $query->order('create_time', 'desc'),
        };
    }
}
