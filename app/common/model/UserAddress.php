<?php

declare(strict_types=1);

namespace app\common\model;

use think\Model;

/**
 * 用户地址模型 - 仅处理数据库操作
 */
class UserAddress extends Model
{
    protected $table = 'user_address';

    protected $type = [
        'user_id' => 'integer',
        'consignee' => 'string',
        'mobile' => 'string',
        'province' => 'string',
        'city' => 'string',
        'district' => 'string',
        'address' => 'string',
        'zipcode' => 'string',
        'is_default' => 'integer',
        'longitude' => 'float',
        'latitude' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getFullAddressAttr()
    {
        return $this->province . $this->city . $this->district . $this->address;
    }

    public function setMobileAttr($value)
    {
        return encrypt_mobile($value);
    }

    public function getMobileAttr($value)
    {
        return $value ? decrypt_mobile($value) : '';
    }
}
