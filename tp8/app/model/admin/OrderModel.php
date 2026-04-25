<?php
declare(strict_types=1);

namespace app\model\admin;

use think\Model;
use think\model\relation\HasMany;

/**
 * @property int $id
 * @property string $order_no
 * @property int $user_id
 * @property string $receiver_name
 * @property string $receiver_mobile
 * @property string|null $receiver_province
 * @property string|null $receiver_city
 * @property string|null $receiver_district
 * @property string $receiver_address
 * @property float $total_amount
 * @property float $discount_amount
 * @property float $pay_amount
 * @property float $points_amount
 * @property float $freight_amount
 * @property int|null $pay_type
 * @property string|null $pay_time
 * @property string|null $pay_no
 * @property string|null $express_company
 * @property string|null $express_no
 * @property string|null $ship_time
 * @property int $status
 * @property string|null $remark
 * @property string $create_time
 * @property string|null $update_time
 */
class OrderModel extends Model
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

    public function items(): HasMany
    {
        return $this->hasMany(OrderItemModel::class, 'order_id');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isPaid(): bool
    {
        return in_array($this->status, [self::STATUS_PAID, self::STATUS_SHIPPED, self::STATUS_RECEIVED]);
    }

    public function canCancel(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function canShip(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function canRefund(): bool
    {
        return in_array($this->status, [self::STATUS_PAID, self::STATUS_SHIPPED]);
    }

    public function getStatusTextAttribute(): string
    {
        $map = [
            self::STATUS_PENDING => '待付款',
            self::STATUS_PAID => '待发货',
            self::STATUS_SHIPPED => '待收货',
            self::STATUS_RECEIVED => '已完成',
            self::STATUS_CANCELLED => '已取消',
            self::STATUS_REFUNDED => '已退款'
        ];
        return $map[$this->status] ?? '未知';
    }

    public function getPayTypeTextAttribute(): string
    {
        $map = [1 => '微信', 2 => '支付宝', 3 => '余额'];
        return $map[$this->pay_type] ?? '未支付';
    }

    public function getPayAmountFormatAttribute(): string
    {
        return '￥' . number_format($this->pay_amount, 2);
    }

    public function getReceiverFullAddressAttribute(): string
    {
        return ($this->receiver_province ?? '') . ($this->receiver_city ?? '') . ($this->receiver_district ?? '') . $this->receiver_address;
    }
}