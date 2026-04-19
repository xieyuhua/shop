<?php

declare(strict_types=1);

namespace app\common\model;

use think\Model;

/**
 * 订单商品模型 - 仅处理数据库操作
 */
class OrderGoods extends Model
{
    protected $table = 'order_goods';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    protected $type = [
        'order_id' => 'integer',
        'product_id' => 'integer',
        'sku_id' => 'integer',
        'shop_id' => 'integer',
        'user_id' => 'integer',
        'goods_name' => 'string',
        'goods_image' => 'string',
        'sku_name' => 'string',
        'price' => 'float',
        'num' => 'integer',
        'total_price' => 'float',
        'is_comment' => 'integer',
    ];

    // 关联关系
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function sku()
    {
        return $this->belongsTo(ProductSku::class, 'sku_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
