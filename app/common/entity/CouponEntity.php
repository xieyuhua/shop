<?php

declare(strict_types=1);

namespace app\common\entity;

use app\common\model\Coupon as CouponModel;
use app\common\model\UserCoupon as UserCouponModel;

/**
 * 优惠券实体 - 处理优惠券相关业务逻辑
 */
class CouponEntity
{
    /**
     * 获取优惠券列表
     */
    public function getList(array $params): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $limit = min(50, max(1, (int) ($params['limit'] ?? 15)));
        $shopId = (int) ($params['shop_id'] ?? 0);

        $query = CouponModel::where('is_show', 1);

        // 店铺筛选（平台券或店铺券）
        if ($shopId > 0) {
            $query->where(function ($q) use ($shopId) {
                $q->where('shop_id', 0)->whereOr('shop_id', $shopId);
            });
        }

        // 库存筛选
        $query->where(function ($q) {
            $q->where('total_num', 0)->whereOr('send_num', '<', 'total_num');
        });

        // 有效期筛选
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
     * 获取我的优惠券
     */
    public function getMyList(int $userId, array $params): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $limit = min(50, max(1, (int) ($params['limit'] ?? 15)));
        $status = $params['status'] ?? '';

        $query = UserCouponModel::with(['coupon', 'shop'])
            ->where('user_id', $userId);

        // 状态筛选
        if ($status === 'unused') {
            $query->where('status', UserCouponModel::STATUS_UNUSED)
                ->where('end_time', '>=', time());
        } elseif ($status === 'used') {
            $query->where('status', UserCouponModel::STATUS_USED);
        } elseif ($status === 'expired') {
            $query->where('status', UserCouponModel::STATUS_UNUSED)
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
     * 领取优惠券
     */
    public function receive(int $userId, int $couponId): array
    {
        // 参数校验
        if (empty($couponId)) {
            return ['success' => false, 'msg' => '请选择优惠券'];
        }

        // 优惠券是否存在
        $coupon = CouponModel::find($couponId);
        if (!$coupon) {
            return ['success' => false, 'msg' => '优惠券不存在'];
        }

        // 是否支持领取
        if ($coupon->send_type != CouponModel::SEND_TYPE_PUBLISH) {
            return ['success' => false, 'msg' => '该优惠券不支持领取'];
        }

        // 执行领取
        $result = CouponModel::receive($couponId, $userId);
        if (!$result) {
            return ['success' => false, 'msg' => '领取失败，请检查是否已领取或优惠券已发完'];
        }

        return ['success' => true, 'data' => $result];
    }

    /**
     * 获取可用优惠券
     */
    public function getAvailable(int $userId, float $money = 0, int $shopId = 0, int $categoryId = 0, int $productId = 0): array
    {
        return UserCouponModel::getAvailableCoupons($userId, $money, $shopId, $categoryId, $productId);
    }

    /**
     * 获取优惠券详情
     */
    public function getDetail(int $id): ?array
    {
        $coupon = CouponModel::with(['shop'])->find($id);
        return $coupon ? $coupon->toArray() : null;
    }
}
