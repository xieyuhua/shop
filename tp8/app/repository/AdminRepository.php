<?php
declare(strict_types=1);

namespace app\repository;

use app\entity\AdminEntity;

class AdminRepository extends Repository
{
    protected $model = 'admin.Admin';

    public function findByUsername(string $username): ?AdminEntity
    {
        return AdminEntity::where('username', $username)->find();
    }

    public function findByMobile(string $mobile): ?AdminEntity
    {
        return AdminEntity::where('phone', $mobile)->find();
    }

    public function getActiveList(): array
    {
        return AdminEntity::where('status', 1)->order('id', 'asc')->select()->toArray();
    }

    public function getAllList(): array
    {
        return AdminEntity::order('id', 'desc')->select()->toArray();
    }
}