<?php
declare(strict_types=1);

namespace app\model\admin;

use think\Model;

class OrderItem extends Model
{
    protected $name = 'order_item';
    protected $pk = 'id';
    protected $autoWriteTimestamp = false;

    public function product()
    {
        return $this->belongsTo('Product', 'product_id');
    }
}