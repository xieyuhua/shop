<?php
declare(strict_types=1);

namespace app\entity;

class OrderItemEntity extends Entity
{
    protected $name = 'order_item';
    protected $pk = 'id';
    protected $createTime = 'create_time';

    public function getTotalPriceFormat(): string
    {
        return '￥' . number_format($this->total_price, 2);
    }

    public function getPriceFormat(): string
    {
        return '￥' . number_format($this->price, 2);
    }

    public function getSpecsText(): string
    {
        return $this->specs ?: '-';
    }
}