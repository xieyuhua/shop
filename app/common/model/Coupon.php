<?php

declare(strict_types=1);

namespace app\common\model;

use think\Model;

class Coupon extends Model
{
    protected $table = 'coupon';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    protected $type = [
        'shop_id' => 'integer',
        'name' => 'string',
        'type' => 'integer',
        'money' => 'float',
        'discount' => 'float',
        'min_money' => 'float',
        'max_money' => 'float',
        'send_type' => 'integer',
        'total_num' => 'integer',
        'send_num' => 'integer',
        'receive_num' => 'integer',
        'use_num' => 'integer',
        'each_limit' => 'integer',
        'start_time' => 'integer',
        'end_time' => 'integer',
        'valid_days' => 'integer',
        'category_ids' => 'string',
        'product_ids' => 'string',
        'is_show' => 'integer',
        'status' => 'integer',
    ];

    const TYPE_MONEY = 1;
    const TYPE_DISCOUNT = 2;

    const SEND_TYPE_PUBLISH = 1;
    const SEND_TYPE_USER = 2;
    const SEND_TYPE_ORDER = 3;
    const SEND_TYPE_REGISTER = 4;

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    public function userCoupons()
    {
        return $this->hasMany(UserCoupon::class, 'coupon_id');
    }

    public function getCategoryIdsAttr($value)
    {
        return $value ? explode(',', $value) : [];
    }

    public function setCategoryIdsAttr($value)
    {
        return is_array($value) ? implode(',', $value) : $value;
    }

    public function getProductIdsAttr($value)
    {
        return $value ? explode(',', $value) : [];
    }

    public function setProductIdsAttr($value)
    {
        return is_array($value) ? implode(',', $value) : $value;
    }

    public function canUse($money, $categoryId = 0, $productId = 0)
    {
        if ($money < $this->min_money) {
            return false;
        }

        if ($this->category_ids) {
            if (!in_array($categoryId, $this->category_ids)) {
                return false;
            }
        }

        if ($this->product_ids) {
            if (!in_array($productId, $this->product_ids)) {
                return false;
            }
        }

        return true;
    }

    public function getDiscountMoney($money)
    {
        if ($this->type == self::TYPE_MONEY) {
            return min($this->money, $money);
        }

        if ($this->type == self::TYPE_DISCOUNT) {
            $discount = $money * (1 - $this->discount / 10);
            if ($this->max_money > 0) {
                $discount = min($discount, $this->max_money);
            }
            return round($discount, 2);
        }

        return 0;
    }

    public function isValid()
    {
        if (!$this->is_show || $this->status == 0) {
            return false;
        }

        if ($this->total_num > 0 && $this->send_num >= $this->total_num) {
            return false;
        }

        $now = time();
        if ($this->start_time > 0 && $now < $this->start_time) {
            return false;
        }

        if ($this->end_time > 0 && $now > $this->end_time) {
            return false;
        }

        return true;
    }

    public static function receive($couponId, $userId)
    {
        $coupon = self::find($couponId);
        if (!$coupon || !$coupon->isValid()) {
            return false;
        }

        $userCouponCount = UserCoupon::where('coupon_id', $couponId)
            ->where('user_id', $userId)
            ->count();

        if ($coupon->each_limit > 0 && $userCouponCount >= $coupon->each_limit) {
            return false;
        }

        $userCoupon = new UserCoupon();
        $userCoupon->coupon_id = $couponId;
        $userCoupon->user_id = $userId;
        $userCoupon->shop_id = $coupon->shop_id;
        $userCoupon->status = 0;

        if ($coupon->valid_days > 0) {
            $userCoupon->start_time = time();
            $userCoupon->end_time = time() + $coupon->valid_days * 86400;
        } else {
            $userCoupon->start_time = $coupon->start_time;
            $userCoupon->end_time = $coupon->end_time;
        }

        $userCoupon->save();

        $coupon->send_num = $coupon->send_num + 1;
        $coupon->save();

        return $userCoupon;
    }
}
