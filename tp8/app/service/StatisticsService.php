<?php
declare(strict_types=1);

namespace app\service;

use app\model\admin\OrderModel;
use app\model\admin\UserModel;
use app\model\admin\ProductModel;

class StatisticsService extends Service
{
    protected string $model = '';

    public function index(string $type = 'today'): array
    {
        $today = date('Y-m-d');
        $last7days = date('Y-m-d', strtotime('-7 days'));
        $last30days = date('Y-m-d', strtotime('-30 days'));

        $whereMap = [
            'today' => ['create_time', 'like', "{$today}%"],
            '7days' => ['create_time', '>=', $last7days],
            '30days' => ['create_time', '>=', $last30days],
        ];

        $where = $whereMap[$type] ?? $whereMap['today'];

        return $this->success([
            'order_count' => OrderModel::where('status', 'in', '0,1,2,3')->count(),
            'order_amount' => OrderModel::where('status', 'in', '0,1,2,3')->sum('pay_amount') ?: 0,
            'user_count' => UserModel::count(),
            'product_count' => ProductModel::where('status', 1)->count(),
            'pending_payment' => OrderModel::where('status', 0)->count(),
            'pending_ship' => OrderModel::where('status', 1)->count(),
            'pending_receive' => OrderModel::where('status', 2)->count(),
            'today_order' => OrderModel::where('create_time', 'like', "{$today}%")->count(),
            'today_sales' => OrderModel::where('create_time', 'like', "{$today}%")->sum('pay_amount') ?: 0,
            'today_user' => UserModel::where('create_time', 'like', "{$today}%")->count(),
            'today_product' => ProductModel::where('status', 1)->count(),
        ]);
    }

    public function chart(int $days = 7): array
    {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));

        return OrderModel::where('status', 'in', '0,1,2,3')
            ->where('create_time', '>=', $startDate)
            ->field("date(create_time) as date, count(*) as count, sum(pay_amount) as amount")
            ->group('date')
            ->select()
            ->toArray();
    }
}