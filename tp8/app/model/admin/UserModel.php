<?php
declare(strict_types=1);

namespace app\model\admin;

use think\Model;

/**
 * @property int $id
 * @property string|null $username
 * @property string $mobile
 * @property string|null $email
 * @property string $password
 * @property string|null $nickname
 * @property string|null $avatar
 * @property int $gender
 * @property float $balance
 * @property int $points
 * @property int $status
 * @property string $create_time
 * @property string|null $update_time
 */
class UserModel extends Model
{
    protected $name = 'user';
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

    public function getGenderTextAttribute(): string
    {
        return [0 => '未知', 1 => '男', 2 => '女'][$this->gender] ?? '未知';
    }

    public function getStatusTextAttribute(): string
    {
        return $this->status === 1 ? '正常' : '禁用';
    }

    public function getBalanceFormatAttribute(): string
    {
        return '￥' . number_format($this->balance, 2);
    }

    public function toSafe(): array
    {
        $data = $this->toArray();
        unset($data['password']);
        return $data;
    }
}