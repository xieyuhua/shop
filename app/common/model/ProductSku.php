<?php

declare(strict_types=1);

namespace app\common\model;

use think\Model;

class ProductSku extends Model
{
    protected $table = 'product_sku';

    protected $type = [
        'product_id' => 'integer',
        'sku_name' => 'string',
        'sku_code' => 'string',
        'price' => 'float',
        'original_price' => 'float',
        'cost_price' => 'float',
        'stock' => 'integer',
        'sales' => 'integer',
        'weight' => 'float',
        'image' => 'string',
        'specs' => 'string',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function getSpecsAttr($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    public function setSpecsAttr($value)
    {
        return is_array($value) ? json_encode($value) : $value;
    }

    public function decreaseStock($num)
    {
        if ($this->stock < $num) {
            return false;
        }
        $this->stock = $this->stock - $num;
        $this->sales = $this->sales + $num;
        return $this->save();
    }

    public function increaseStock($num)
    {
        $this->stock = $this->stock + $num;
        $this->sales = max(0, $this->sales - $num);
        return $this->save();
    }
}
