<?php
declare(strict_types=1);

namespace app\entity;

class ConfigEntity extends Entity
{
    protected $name = 'config';
    protected $pk = 'id';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    public function getValueFormat(): string
    {
        return $this->value ?? '';
    }
}