<?php
declare(strict_types=1);

namespace app\model\admin;

use think\Model;

/**
 * @property int $id
 * @property int $order_id
 * @property string $order_no
 * @property int $product_id
 * @property string $product_name
 * @property string|null $product_image
 * @property float $price
 * @property string|null $specs
 * @property int $quantity
 * @property float $total_price
 * @property string $create_time
 */
class OrderItemModel extends Model
{
    protected $name = 'order_item';
    protected $pk = 'id';
    protected $createTime = 'create_time';

    public function getTotalPriceFormatAttribute(): string
    {
        return '￥' . number_format($this->total_price, 2);
    }
}