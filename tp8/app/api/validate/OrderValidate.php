<?php
declare(strict_types=1);

namespace app\api\validate;

use think\Validate;

class OrderValidate extends Validate
{
    protected $rule = [
        'express_company' => 'require|max:50',
        'express_no' => 'require|max:50',
    ];

    protected $message = [
        'express_company.require' => '请输入快递公司',
        'express_company.max' => '快递公司最多50个字符',
        'express_no.require' => '请输入快递单号',
        'express_no.max' => '快递单号最多50个字符',
    ];

    protected $scene = [
        'ship' => ['express_company', 'express_no'],
    ];
}