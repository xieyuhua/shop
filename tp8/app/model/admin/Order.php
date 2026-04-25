<?php
declare(strict_types=1);

namespace app\model\admin;

use think\Model;

class Order extends Model
{
    protected $name = 'order';
    protected $pk = 'id';
    protected $autoWriteTimestamp = 'datetime';
    protected $updateTime = 'update_time';
    protected $createTime = 'create_time';

    public function user()
    {
        return $this->belongsTo('User', 'user_id');
    }

    public function items()
    {
        return $this->hasMany('OrderItem', 'order_id');
    }

    public static function getStatusList()
    {
        return [0 => '待付款', 1 => '待发货', 2 => '待收货', 3 => '已完成', 4 => '已取消', 5 => '已退款'];
    }

    public static function getPayTypeList()
    {
        return [1 => '微信', 2 => '支付宝', 3 => '余额'];
    }
}