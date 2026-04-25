<?php
declare(strict_types=1);

namespace app\entity;

class UserEntity extends Entity
{
    protected $name = 'user';
    protected $pk = 'id';
    protected $autoWriteTimestamp = 'datetime';
    protected $updateTime = 'update_time';
    protected $createTime = 'create_time';

    protected $hidden = ['password'];

    public function setPasswordAttr(string $value): string
    {
        return password_hash($value, PASSWORD_DEFAULT);
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    public function isActive(): bool
    {
        return $this->status == 1;
    }

    public function getStatusText(): string
    {
        $map = [0 => '禁用', 1 => '正常'];
        return $map[$this->status] ?? '未知';
    }

    public function getGenderText(): string
    {
        $map = [0 => '未知', 1 => '男', 2 => '女'];
        return $map[$this->gender] ?? '未知';
    }

    public function getBalanceFormat(): string
    {
        return '￥' . number_format($this->balance, 2);
    }

    public function safeData(): array
    {
        $data = $this->toArray();
        unset($data['password']);
        return $data;
    }

    public function maskMobile(): string
    {
        return substr($this->mobile, 0, 3) . '****' . substr($this->mobile, -4);
    }
}