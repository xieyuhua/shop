<?php

declare(strict_types=1);

namespace app\common\entity;

use app\common\model\Shop as ShopModel;

/**
 * 店铺实体 - 处理店铺相关业务逻辑
 */
class ShopEntity
{
    private ShopModel $model;

    public function __construct()
    {
        $this->model = new ShopModel();
    }

    /**
     * 获取店铺列表
     */
    public function getList(array $params): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $limit = min(50, max(1, (int) ($params['limit'] ?? 15)));
        $keyword = $params['keyword'] ?? '';
        $status = $params['status'] ?? '';

        $query = ShopModel::with(['user', 'category'])->order('id', 'desc');

        // 关键词搜索（防注入）
        if (!empty($keyword)) {
            $keyword = addcslashes($keyword, '%_');
            $query->where('shop_name', 'like', '%' . $keyword . '%');
        }

        // 状态筛选
        if ($status !== '') {
            $query->where('status', (int) $status);
        }

        $total = $query->count();
        $list = $query->page($page, $limit)->select();

        return [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ];
    }

    /**
     * 获取店铺详情
     */
    public function getDetail(int $id): ?array
    {
        $shop = ShopModel::with(['user', 'category'])->find($id);
        return $shop ? $shop->toArray() : null;
    }

    /**
     * 审核店铺
     */
    public function audit(int $id, int $status, string $reason = ''): array
    {
        // 参数校验
        if (!in_array($status, [ShopModel::STATUS_PASS, ShopModel::STATUS_REJECTED])) {
            return ['success' => false, 'msg' => '审核状态不正确'];
        }

        $shop = ShopModel::find($id);
        if (!$shop) {
            return ['success' => false, 'msg' => '店铺不存在'];
        }

        if ($shop->status != ShopModel::STATUS_PENDING) {
            return ['success' => false, 'msg' => '店铺状态不允许审核'];
        }

        $shop->status = $status;
        if ($status == ShopModel::STATUS_REJECTED) {
            $shop->reject_reason = $reason;
        }
        $shop->audit_time = time();
        $shop->save();

        return ['success' => true];
    }

    /**
     * 设置店铺状态
     */
    public function setStatus(int $id, int $status): array
    {
        $shop = ShopModel::find($id);
        if (!$shop) {
            return ['success' => false, 'msg' => '店铺不存在'];
        }

        $shop->status = $status;
        $shop->save();

        return ['success' => true];
    }

    /**
     * 获取店铺统计
     */
    public function getStats(int $shopId): array
    {
        $shop = ShopModel::find($shopId);
        if (!$shop) {
            return [];
        }

        $orderCount = \app\common\model\Order::where('shop_id', $shopId)->count();
        $productCount = \app\common\model\Product::where('shop_id', $shopId)->where('is_on_sale', 1)->count();

        return [
            'order_count' => $orderCount,
            'product_count' => $productCount,
        ];
    }
}
