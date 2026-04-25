<?php
declare(strict_types=1);

namespace app\model\admin;

use think\Model;

/**
 * @property int $id
 * @property string $username
 * @property string $password
 * @property string|null $nickname
 * @property string|null $avatar
 * @property string|null $phone
 * @property string|null $email
 * @property int $status
 * @property string|null $login_ip
 * @property string|null $login_time
 * @property string $create_time
 * @property string|null $update_time
 */
class AdminModel extends Model
{
    protected $name = 'admin';
    protected $pk = 'id';
    protected $autoWriteTimestamp = 'datetime';
    protected $updateTime = 'update_time';
    protected $createTime = 'create_time';

    public function setPasswordAttr($value): string
    {
        return password_hash($value, PASSWORD_DEFAULT);
    }

    public function checkPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    public function isActive(): bool
    {
        return $this->status === 1;
    }

    public function toSafe(): array
    {
        $data = $this->toArray();
        unset($data['password']);
        return $data;
    }
}