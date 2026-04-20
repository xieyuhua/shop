<?php

declare(strict_types=1);

namespace app\common\entity;

use app\common\model\Shop;
use app\common\model\User;
use app\common\model\Category;
use app\common\model\Order;
use app\common\model\Product;

/**
 * 店铺实体
 */
class ShopEntity extends BaseEntity
{
    protected $table = 'shop';

    protected $type = [
        'longitude' => 'float',
        'latitude' => 'float',
    ];

    // ========== 业务逻辑 ==========

    /**
     * 获取店铺列表
     */
    public function getList(array $params): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $limit = min(50, max(1, (int) ($params['limit'] ?? 15)));
        $keyword = $params['keyword'] ?? '';
        $status = $params['status'] ?? '';

        $query = self::with(['user', 'category'])->order('id', 'desc');

        if (!empty($keyword)) {
            $keyword = addcslashes($keyword, '%_');
            $query->where('shop_name', 'like', '%' . $keyword . '%');
        }

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
        $shop = self::with(['user', 'category'])->find($id);
        return $shop ? $shop->toArray() : null;
    }

    /**
     * 审核店铺
     */
    public function audit(int $id, int $status, string $reason = ''): array
    {
        if (!in_array($status, [Shop::STATUS_PASS, Shop::STATUS_REJECTED])) {
            return ['success' => false, 'msg' => '审核状态不正确'];
        }

        $shop = self::find($id);
        if (!$shop) {
            return ['success' => false, 'msg' => '店铺不存在'];
        }

        if ($shop->status != Shop::STATUS_PENDING) {
            return ['success' => false, 'msg' => '店铺状态不允许审核'];
        }

        $shop->status = $status;
        if ($status == Shop::STATUS_REJECTED) {
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
        $shop = self::find($id);
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
        $shop = self::find($shopId);
        if (!$shop) {
            return [];
        }

        $orderCount = Order::where('shop_id', $shopId)->count();
        $productCount = Product::where('shop_id', $shopId)->where('is_on_sale', 1)->count();

        return [
            'order_count' => $orderCount,
            'product_count' => $productCount,
        ];
    }
}
