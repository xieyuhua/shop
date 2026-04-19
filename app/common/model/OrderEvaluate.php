<?php

declare(strict_types=1);

namespace app\common\model;

use think\Model;

/**
 * 订单评价模型 - 仅处理数据库操作
 */
class OrderEvaluate extends Model
{
    protected $table = 'order_evaluate';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    protected $type = [
        'order_id' => 'integer',
        'order_goods_id' => 'integer',
        'product_id' => 'integer',
        'shop_id' => 'integer',
        'user_id' => 'integer',
        'score' => 'integer',
        'score_describe' => 'integer',
        'score_service' => 'integer',
        'score_logistics' => 'integer',
        'content' => 'string',
        'images' => 'string',
        'reply' => 'string',
        'reply_time' => 'integer',
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

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    // 获取格式化图片
    public function getImagesListAttr()
    {
        return $this->images ? json_decode($this->images, true) : [];
    }
}
