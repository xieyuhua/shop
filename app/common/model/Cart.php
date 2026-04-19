<?php

declare(strict_types=1);

namespace app\common\model;

use think\Model;

/**
 * 购物车模型 - 仅处理数据库操作
 */
class Cart extends Model
{
    protected $table = 'cart';

    protected $type = [
        'user_id' => 'integer',
        'product_id' => 'integer',
        'sku_id' => 'integer',
        'shop_id' => 'integer',
        'num' => 'integer',
        'selected' => 'integer',
    ];

    // 关联关系
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function sku()
    {
        return $this->belongsTo(ProductSku::class, 'sku_id');
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    // 计算属性
    public function getPriceAttr()
    {
        if ($this->sku_id > 0) {
            return $this->sku ? $this->sku->price : 0;
        }
        return $this->product ? $this->product->price : 0;
    }

    public function getTotalPriceAttr()
    {
        return $this->price * $this->num;
    }
}
