<?php

declare(strict_types=1);

namespace app\common\model;

use think\Model;

/**
 * 售后模型 - 仅处理数据库操作
 */
class OrderAftersale extends Model
{
    protected $table = 'order_aftersale';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    protected $type = [
        'order_id' => 'integer',
        'user_id' => 'integer',
        'shop_id' => 'integer',
        'type' => 'integer',
        'reason' => 'string',
        'description' => 'string',
        'refund_money' => 'float',
        'images' => 'string',
        'status' => 'integer',
        'express_company' => 'string',
        'express_no' => 'string',
        'deliver_time' => 'integer',
        'refund_time' => 'integer',
        'refuse_reason' => 'string',
        'cancel_time' => 'integer',
    ];

    // 售后类型
    const TYPE_REFUND = 1;      // 仅退款
    const TYPE_RETURN = 2;       // 退货退款

    // 售后状态
    const STATUS_PENDING = 0;       // 待处理
    const STATUS_PROCESSING = 1;    // 处理中
    const STATUS_AGREE = 2;          // 已同意
    const STATUS_REFUSE = 3;         // 已拒绝
    const STATUS_CANCELLED = 4;     // 已取消

    // 关联关系
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    public function getTypeTextAttr()
    {
        return $this->type == self::TYPE_REFUND ? '仅退款' : '退货退款';
    }

    public function getStatusTextAttr()
    {
        $status = [
            self::STATUS_PENDING => '待处理',
            self::STATUS_PROCESSING => '处理中',
            self::STATUS_AGREE => '已同意',
            self::STATUS_REFUSE => '已拒绝',
            self::STATUS_CANCELLED => '已取消',
        ];
        return $status[$this->status] ?? '';
    }
}
