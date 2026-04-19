<?php

declare(strict_types=1);

namespace app\common\model;

use think\Model;

/**
 * 管理员角色模型 - 仅处理数据库操作
 */
class AdminRole extends Model
{
    protected $table = 'admin_role';

    protected $type = [
        'name' => 'string',
        'description' => 'string',
        'permissions' => 'string',
        'status' => 'integer',
    ];

    public function admins()
    {
        return $this->hasMany(Admin::class, 'role_id');
    }

    public function getPermissionsListAttr()
    {
        return $this->permissions ? json_decode($this->permissions, true) : [];
    }
}
