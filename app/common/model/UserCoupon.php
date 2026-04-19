<?php

declare(strict_types=1);

namespace app\common\model;

use think\Model;

class UserCoupon extends Model
{
    protected $table = 'user_coupon';

    protected $type = [
        'user_id' => 'integer',
        'coupon_id' => 'integer',
        'shop_id' => 'integer',
        'order_id' => 'integer',
        'status' => 'integer',
        'start_time' => 'integer',
        'end_time' => 'integer',
        'use_time' => 'integer',
    ];

    const STATUS_UNUSED = 0;
    const STATUS_USED = 1;
    const STATUS_EXPIRED = 2;

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function getIsValidAttr()
    {
        $now = time();
        if ($this->status != self::STATUS_UNUSED) {
            return false;
        }
        if ($this->start_time > $now || $this->end_time < $now) {
            return false;
        }
        return true;
    }

    public function useIt($orderId)
    {
        $this->status = self::STATUS_USED;
        $this->order_id = $orderId;
        $this->use_time = time();
        $this->save();

        $coupon = $this->coupon;
        if ($coupon) {
            $coupon->use_num = $coupon->use_num + 1;
            $coupon->save();
        }
    }

    public static function getUserCoupons($userId, $status = null)
    {
        $query = self::with(['coupon', 'shop'])
            ->where('user_id', $userId);

        if ($status !== null) {
            $query->where('status', $status);
        }

        return $query->order('create_time', 'desc')->select();
    }

    public static function getAvailableCoupons($userId, $money, $shopId = 0, $categoryId = 0, $productId = 0)
    {
        $now = time();

        $query = self::with(['coupon'])
            ->where('user_id', $userId)
            ->where('status', self::STATUS_UNUSED)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now);

        $coupons = $query->select();

        $result = [];
        foreach ($coupons as $userCoupon) {
            $coupon = $userCoupon->coupon;
            if (!$coupon) continue;

            if ($shopId > 0 && $coupon->shop_id > 0 && $coupon->shop_id != $shopId) {
                continue;
            }

            if (!$coupon->canUse($money, $categoryId, $productId)) {
                continue;
            }

            $result[] = $userCoupon;
        }

        return $result;
    }
}
