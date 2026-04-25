<?php
declare(strict_types=1);

namespace app\model\admin;

use think\Model;

class User extends Model
{
    protected $name = 'user';
    protected $pk = 'id';
    protected $autoWriteTimestamp = 'datetime';
    protected $updateTime = 'update_time';
    protected $createTime = 'create_time';

    protected function setPasswordAttr($value)
    {
        return password_hash($value, PASSWORD_DEFAULT);
    }

    public function checkPassword($password): bool
    {
        return password_verify($password, $this->password);
    }
}