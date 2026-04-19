<?php

declare(strict_types=1);

namespace app\common\model;

use think\Model;

/**
 * 管理员模型 - 仅处理数据库操作
 */
class Admin extends Model
{
    protected $table = 'admin';

    protected $type = [
        'username' => 'string',
        'password' => 'string',
        'nickname' => 'string',
        'avatar' => 'string',
        'role_id' => 'integer',
        'status' => 'integer',
        'last_login_time' => 'timestamp',
        'last_login_ip' => 'string',
    ];

    // 关联关系
    public function role()
    {
        return $this->belongsTo(AdminRole::class, 'role_id');
    }

    public function setPasswordAttr($value)
    {
        return password_hash($value, PASSWORD_DEFAULT);
    }

    public function getAvatarAttr($value)
    {
        return $value ?: '/static/images/admin.png';
    }
}
