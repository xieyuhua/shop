<?php
declare(strict_types=1);

namespace app\repository;

use app\entity\UserEntity;

class UserRepository extends Repository
{
    protected $model = 'admin.User';

    public function findByMobile(string $mobile): ?UserEntity
    {
        return UserEntity::where('mobile', $mobile)->find();
    }

    public function findByEmail(string $email): ?UserEntity
    {
        return UserEntity::where('email', $email)->find();
    }

    public function getActiveList(): array
    {
        return UserEntity::where('status', 1)->order('id', 'desc')->select()->toArray();
    }

    public function search(string $keyword): array
    {
        return UserEntity::where('username|mobile|email|nickname', 'like', "%{$keyword}%")
            ->order('id', 'desc')
            ->select()
            ->toArray();
    }
}