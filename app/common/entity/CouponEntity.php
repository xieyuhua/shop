<?php

declare(strict_types=1);

namespace app\common\entity;

use app\common\model\Coupon;
use app\common\model\UserCoupon;
use app\common\model\Shop;

/**
 * 优惠券实体
 */
class CouponEntity extends BaseEntity
{
    protected $table = 'coupon';

    protected $type = [
        'money' => 'float',
        'min_money' => 'float',
        'total_num' => 'integer',
        'send_num' => 'integer',
    ];

    // ========== 业务逻辑 ==========

    /**
     * 获取优惠券列表
     */
    public function getList(array $params): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $limit = min(50, max(1, (int) ($params['limit'] ?? 15)));
        $shopId = (int) ($params['shop_id'] ?? 0);

        $query = self::where('is_show', 1);

        if ($shopId > 0) {
            $query->where(function ($q) use ($shopId) {
                $q->where('shop_id', 0)->whereOr('shop_id', $shopId);
            });
        }

        $query->where(function ($q) {
            $q->where('total_num', 0)->whereOr('send_num', '<', 'total_num');
        });

        $query->where(function ($q) {
            $q->where('end_time', 0)->whereOr('end_time', '>', time());
        });

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
     * 领取优惠券
     */
    public function receive(int $userId, int $couponId): array
    {
        if (empty($couponId)) {
            return ['success' => false, 'msg' => '请选择优惠券'];
        }

        $coupon = self::find($couponId);
        if (!$coupon) {
            return ['success' => false, 'msg' => '优惠券不存在'];
        }

        if ($coupon->send_type != Coupon::SEND_TYPE_PUBLISH) {
            return ['success' => false, 'msg' => '该优惠券不支持领取'];
        }

        $result = Coupon::receive($couponId, $userId);
        if (!$result) {
            return ['success' => false, 'msg' => '领取失败，请检查是否已领取或优惠券已发完'];
        }

        return ['success' => true, 'data' => $result];
    }

    /**
     * 获取优惠券详情
     */
    public function getDetail(int $id): ?array
    {
        $coupon = self::with(['shop'])->find($id);
        return $coupon ? $coupon->toArray() : null;
    }
}
