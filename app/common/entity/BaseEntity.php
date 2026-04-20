<?php

declare(strict_types=1);

namespace app\common\entity;

use think\Model;

/**
 * 实体基类 - 继承 Model，获得 ORM 能力
 *
 * 设计理念：
 * - Entity = Model + 业务逻辑
 * - 一个 Entity 对应一个 Model
 * - 保持 Model 的数据库特性（软删除、自动时间戳等）
 *
 * @mixin \think\Model
 */
abstract class BaseEntity extends Model
{
    /**
     * 获取当前实体对应的 Model 类名
     */
    abstract protected static function getModelClass(): string;

    /**
     * 初始化 - 自动设置表名
     */
    protected function initialize(): void
    {
        parent::initialize();
        $modelClass = static::getModelClass();
        if ($modelClass && class_exists($modelClass)) {
            $this->name = (new $modelClass())->getName();
        }
    }
}
