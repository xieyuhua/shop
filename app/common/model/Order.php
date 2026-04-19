<?php

declare(strict_types=1);

namespace app\common\model;

use think\Model;

/**
 * 订单模型 - 仅处理数据库操作
 */
class Order extends Model
{
    protected $table = 'order';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    protected $type = [
        'user_id' => 'integer',
        'shop_id' => 'integer',
        'order_no' => 'string',
        'total_num' => 'integer',
        'total_price' => 'float',
        'freight_price' => 'float',
        'discount_price' => 'float',
        'pay_price' => 'float',
        'points_discount' => 'float',
        'coupon_id' => 'integer',
        'coupon_price' => 'float',
        'address_id' => 'integer',
        'pay_type' => 'integer',
        'pay_status' => 'integer',
        'pay_time' => 'integer',
        'delivery_type' => 'integer',
        'express_company' => 'string',
        'express_no' => 'string',
        'delivery_time' => 'integer',
        'receive_time' => 'integer',
        'complete_time' => 'integer',
        'order_status' => 'integer',
        'remark' => 'string',
        'is_comment' => 'integer',
        'is_delete' => 'integer',
        'cancel_time' => 'integer',
        'cancel_reason' => 'string',
    ];

    // 订单状态常量
    const STATUS_PENDING_PAY = 0;
    const STATUS_PENDING_DELIVERY = 1;
    const STATUS_PENDING_RECEIVE = 2;
    const STATUS_PENDING_COMMENT = 3;
    const STATUS_COMPLETED = 4;
    const STATUS_CANCELLED = 5;
    const STATUS_REFUNDING = 6;
    const STATUS_REFUNDED = 7;

    // 支付状态常量
    const PAY_STATUS_UNPAID = 0;
    const PAY_STATUS_PAID = 1;

    // 关联关系
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    public function address()
    {
        return $this->belongsTo(UserAddress::class, 'address_id');
    }

    public function orderGoods()
    {
        return $this->hasMany(OrderGoods::class, 'order_id');
    }

    public function aftersale()
    {
        return $this->hasOne(OrderAftersale::class, 'order_id');
    }

    public function getStatusTextAttr()
    {
        $status = [
            self::STATUS_PENDING_PAY => '待付款',
            self::STATUS_PENDING_DELIVERY => '待发货',
            self::STATUS_PENDING_RECEIVE => '待收货',
            self::STATUS_PENDING_COMMENT => '待评价',
            self::STATUS_COMPLETED => '已完成',
            self::STATUS_CANCELLED => '已取消',
            self::STATUS_REFUNDING => '退款中',
            self::STATUS_REFUNDED => '已退款',
        ];
        return $status[$this->order_status] ?? '';
    }
}
