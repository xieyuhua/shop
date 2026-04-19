<?php

declare(strict_types=1);

namespace app\common\model;

use think\Model;

class ProductSpec extends Model
{
    protected $table = 'product_spec';

    protected $type = [
        'product_id' => 'integer',
        'name' => 'string',
        'values' => 'string',
        'sort' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function getValuesAttr($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    public function setValuesAttr($value)
    {
        return is_array($value) ? json_encode($value) : $value;
    }
}
