<?php

declare(strict_types=1);

namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;

/**
 * 用户模型 - 仅处理数据库操作，不包含业务逻辑
 */
class User extends Model
{
    use SoftDelete;

    protected $table = 'user';
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;

    protected $type = [
        'avatar' => 'string',
        'gender' => 'integer',
        'birthday' => 'timestamp',
        'mobile' => 'string',
        'nickname' => 'string',
        'username' => 'string',
        'email' => 'string',
        'level' => 'integer',
        'points' => 'integer',
        'balance' => 'float',
        'frozen_balance' => 'float',
        'status' => 'integer',
        'last_login_time' => 'timestamp',
        'last_login_ip' => 'string',
    ];

    // 关联关系
    public function addresses()
    {
        return $this->hasMany(UserAddress::class, 'user_id');
    }

    public function defaultAddress()
    {
        return $this->hasOne(UserAddress::class, 'user_id')->where('is_default', 1);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class, 'user_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    public function shop()
    {
        return $this->hasOne(Shop::class, 'user_id');
    }

    public function pointsLogs()
    {
        return $this->hasMany(PointsLog::class, 'user_id');
    }

    public function balanceLogs()
    {
        return $this->hasMany(BalanceLog::class, 'user_id');
    }

    // 字段修改器
    public function setPasswordAttr($value)
    {
        return password_hash($value, PASSWORD_DEFAULT);
    }

    public function setMobileAttr($value)
    {
        return encrypt_mobile($value);
    }

    public function getMobileAttr($value)
    {
        return $value ? decrypt_mobile($value) : '';
    }

    public function getAvatarAttr($value)
    {
        return $value ?: '/static/images/avatar.png';
    }
}
