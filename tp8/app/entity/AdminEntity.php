<?php
declare(strict_types=1);

namespace app\entity;

class AdminEntity extends Entity
{
    protected $name = 'admin';
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

    public function isSuperAdmin(): bool
    {
        return $this->username === 'admin';
    }

    public function getStatusText(): string
    {
        return $this->status ? '正常' : '禁用';
    }

    public function safeData(): array
    {
        $data = $this->toArray();
        unset($data['password']);
        return $data;
    }
}