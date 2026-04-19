<?php

declare(strict_types=1);

namespace app\api\controller;

use app\common\controller\BaseController;
use app\common\model\Coupon;
use app\common\model\UserCoupon;

class CouponController extends BaseController
{
    public function list()
    {
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 15);
        $shopId = $this->request->get('shop_id', 0);

        $query = Coupon::where('is_show', 1);

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

        return $this->success([
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit),
        ]);
    }

    public function myList()
    {
        $this->auth();
        $status = $this->request->get('status', '');
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 15);

        $query = UserCoupon::with(['coupon', 'shop'])
            ->where('user_id', $this->userId);

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

        return $this->success([
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit),
        ]);
    }

    public function receive()
    {
        $this->auth();
        $couponId = $this->request->post('coupon_id');

        if (empty($couponId)) {
            return $this->error('请选择优惠券');
        }

        $coupon = Coupon::find($couponId);
        if (!$coupon) {
            return $this->error('优惠券不存在');
        }

        if ($coupon->send_type != Coupon::SEND_TYPE_PUBLISH) {
            return $this->error('该优惠券不支持领取');
        }

        $result = Coupon::receive($couponId, $this->userId);
        if (!$result) {
            return $this->error('领取失败，请检查是否已领取或优惠券已发完');
        }

        return $this->success($result);
    }

    public function available()
    {
        $this->auth();
        $money = $this->request->get('money', 0);
        $shopId = $this->request->get('shop_id', 0);
        $categoryId = $this->request->get('category_id', 0);
        $productId = $this->request->get('product_id', 0);

        $coupons = UserCoupon::getAvailableCoupons($this->userId, $money, $shopId, $categoryId, $productId);

        return $this->success($coupons);
    }
}
