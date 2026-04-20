<?php

declare(strict_types=1);

namespace app\common\entity;

use app\common\model\UserCoupon;
use app\common\model\Coupon;
use app\common\model\Shop;

/**
 * 用户优惠券实体
 */
class UserCouponEntity extends BaseEntity
{
    protected $table = 'user_coupon';

    protected $type = [
        'money' => 'float',
    ];

    // ========== 业务逻辑 ==========

    /**
     * 获取我的优惠券
     */
    public function getMyList(int $userId, array $params): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $limit = min(50, max(1, (int) ($params['limit'] ?? 15)));
        $status = $params['status'] ?? '';

        $query = self::with(['coupon', 'shop'])
            ->where('user_id', $userId);

        if ($status === 'unused') {
            $query->where('status', UserCoupon::STATUS_UNUSED)
                ->where('end_time', '>=', time());
        } elseif ($status === 'used') {
            $query->where('status', UserCoupon::STATUS_USED);
        } elseif ($status === 'expired') {
            $query->where('status', UserCoupon::STATUS_UNUSED)
                ->where('end_time', '<', time());
        }

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
     * 获取可用优惠券
     */
    public function getAvailable(int $userId, float $money = 0, int $shopId = 0, int $categoryId = 0, int $productId = 0): array
    {
        return UserCoupon::getAvailableCoupons($userId, $money, $shopId, $categoryId, $productId);
    }
}
