<?php
declare(strict_types=1);

namespace app\entity;

class CategoryEntity extends Entity
{
    protected $name = 'category';
    protected $pk = 'id';
    protected $autoWriteTimestamp = 'datetime';
    protected $updateTime = 'update_time';
    protected $createTime = 'create_time';

    public function isTopLevel(): bool
    {
        return $this->pid == 0;
    }

    public function isActive(): bool
    {
        return (bool)$this->status;
    }

    public function getStatusText(): string
    {
        return $this->status ? '显示' : '隐藏';
    }

    public function getFullName(): string
    {
        return $this->name;
    }

    public function getLevel(): int
    {
        return 0;
    }
}