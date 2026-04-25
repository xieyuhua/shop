<?php
declare(strict_types=1);

namespace app\entity;

class OrderEntity extends Entity
{
    protected $name = 'order';
    protected $pk = 'id';
    protected $autoWriteTimestamp = 'datetime';
    protected $updateTime = 'update_time';
    protected $createTime = 'create_time';

    const STATUS_PENDING = 0;
    const STATUS_PAID = 1;
    const STATUS_SHIPPED = 2;
    const STATUS_RECEIVED = 3;
    const STATUS_CANCELLED = 4;
    const STATUS_REFUNDED = 5;

    public function isPaid(): bool
    {
        return in_array($this->status, [1, 2, 3]);
    }

    public function isPending(): bool
    {
        return $this->status == 0;
    }

    public function isCancelled(): bool
    {
        return $this->status == 4;
    }

    public function isRefunded(): bool
    {
        return $this->status == 5;
    }

    public function getStatusText(): string
    {
        $map = [0 => '待付款', 1 => '待发货', 2 => '待收货', 3 => '已完成', 4 => '已取消', 5 => '已退款'];
        return $map[$this->status] ?? '未知';
    }

    public function getPayTypeText(): string
    {
        $map = [1 => '微信', 2 => '支付宝', 3 => '余额'];
        return $map[$this->pay_type] ?? '未支付';
    }

    public function getTotalAmountFormat(): string
    {
        return '￥' . number_format($this->total_amount, 2);
    }

    public function getPayAmountFormat(): string
    {
        return '￥' . number_format($this->pay_amount, 2);
    }

    public function getDiscountAmountFormat(): string
    {
        return '-￥' . number_format($this->discount_amount, 2);
    }

    public function getFreightAmountFormat(): string
    {
        return '￥' . number_format($this->freight_amount, 2);
    }

    public function getReceiverFullAddress(): string
    {
        return ($this->receiver_province ?? '') . ($this->receiver_city ?? '') . ($this->receiver_district ?? '') . $this->receiver_address;
    }

    public function canCancel(): bool
    {
        return $this->status == 0;
    }

    public function canShip(): bool
    {
        return $this->status == 1;
    }

    public function canRefund(): bool
    {
        return in_array($this->status, [1, 2]);
    }
}