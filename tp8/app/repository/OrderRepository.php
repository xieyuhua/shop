<?php
declare(strict_types=1);

namespace app\repository;

use app\entity\OrderEntity;
use app\entity\OrderItemEntity;

class OrderRepository extends Repository
{
    protected $model = 'admin.Order';

    public function findByOrderNo(string $orderNo): ?OrderEntity
    {
        return OrderEntity::where('order_no', $orderNo)->find();
    }

    public function findByUser(int $userId): array
    {
        return OrderEntity::where('user_id', $userId)
            ->order('id', 'desc')
            ->select()
            ->toArray();
    }

    public function findItems(int $orderId): array
    {
        return OrderItemEntity::where('order_id', $orderId)->select()->toArray();
    }

    public function getPendingPayment(): array
    {
        return OrderEntity::where('status', 0)->select()->toArray();
    }

    public function getPendingShip(): array
    {
        return OrderEntity::where('status', 1)->select()->toArray();
    }

    public function getCompleted(): array
    {
        return OrderEntity::where('status', 3)->order('id', 'desc')->select()->toArray();
    }

    public function countByStatus(int $status): int
    {
        return OrderEntity::where('status', $status)->count();
    }
}