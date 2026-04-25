<?php
declare(strict_types=1);

namespace app\api\validate;

use think\Validate;

class ProductValidate extends Validate
{
    protected $rule = [
        'name' => 'require|max:200',
        'slug' => 'max:200',
        'category_id' => 'require|number',
        'price' => 'require|float|min:0',
        'original_price' => 'float|min:0',
        'cost_price' => 'float|min:0',
        'stock' => 'number|min:0',
        'status' => 'number|in:0,1',
    ];

    protected $message = [
        'name.require' => '商品名称不能为空',
        'name.max' => '商品名称最多200个字符',
        'slug.max' => '商品别名最多200个字符',
        'category_id.require' => '请选择分类',
        'category_id.number' => '分类ID必须是数字',
        'price.require' => '价格不能为空',
        'price.float' => '价格必须是数字',
        'price.min' => '价格不能小于0',
        'original_price.float' => '原价必须是数字',
        'original_price.min' => '原价不能小于0',
        'cost_price.float' => '成本价必须是数字',
        'cost_price.min' => '成本价不能小于0',
        'stock.number' => '库存必须是整数',
        'stock.min' => '库存不能小于0',
        'status.number' => '状态必须是数字',
        'status.in' => '状态值不正确',
    ];

    protected $scene = [
        'save' => ['name', 'category_id', 'price'],
    ];
}