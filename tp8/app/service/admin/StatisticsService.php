<?php
declare(strict_types=1);

namespace app\service\admin;

use app\entity\OrderEntity;
use app\entity\UserEntity;
use app\entity\ProductEntity;

class StatisticsService extends Service
{
    public function getStatistics(string $type = 'today'): array
    {
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $last7days = date('Y-m-d', strtotime('-7 days'));
        $last30days = date('Y-m-d', strtotime('-30 days'));
        
        $orderWhere = [['status', 'in', '0,1,2,3']];
        $userWhere = [];
        
        switch ($type) {
            case 'today':
                $orderWhere[] = ['create_time', 'like', "{$today}%"];
                $userWhere[] = ['create_time', 'like', "{$today}%"];
                break;
            case '7days':
                $orderWhere[] = ['create_time', '>=', $last7days];
                $userWhere[] = ['create_time', '>=', $last7days];
                break;
            case '30days':
                $orderWhere[] = ['create_time', '>=', $last30days];
                $userWhere[] = ['create_time', '>=', $last30days];
                break;
        }
        
        return [
            'order_count' => OrderEntity::where($orderWhere)->count(),
            'order_amount' => OrderEntity::where($orderWhere)->sum('pay_amount'),
            'user_count' => UserEntity::where($userWhere)->count(),
            'product_count' => ProductEntity::where('status', 1)->count(),
            'pending_payment' => OrderEntity::where('status', 0)->count(),
            'pending_ship' => OrderEntity::where('status', 1)->count(),
            'pending_receive' => OrderEntity::where('status', 2)->count(),
        ];
    }

    public function getChartData(int $days = 7): array
    {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));
        
        return OrderEntity::where('status', 'in', '0,1,2,3')
            ->where('create_time', '>=', $startDate)
            ->field("from_unixtime(unix_timestamp(create_time), '%Y-%m-%d') as date, count(*) as count, sum(pay_amount) as amount")
            ->group('date')
            ->select()
            ->toArray();
    }

    public function export(): array
    {
        return OrderEntity::order('id', 'desc')
            ->select()
            ->toArray();
    }
}