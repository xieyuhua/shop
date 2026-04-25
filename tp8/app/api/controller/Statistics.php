<?php
declare(strict_types=1);

namespace app\api\controller;

use app\model\admin\OrderModel;
use app\model\admin\UserModel;
use app\model\admin\ProductModel;

class Statistics extends ApiController
{
    public function index()
    {
        $type = $this->request->param('type', 'today');
        
        $today = date('Y-m-d');
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
        
        $stats = [
            'order_count' => OrderModel::where($orderWhere)->count(),
            'order_amount' => OrderModel::where($orderWhere)->sum('pay_amount') ?: 0,
            'user_count' => UserModel::where($userWhere)->count(),
            'product_count' => ProductModel::where('status', 1)->count(),
            'pending_payment' => OrderModel::where('status', 0)->count(),
            'pending_ship' => OrderModel::where('status', 1)->count(),
            'pending_receive' => OrderModel::where('status', 2)->count(),
            'today_order' => OrderModel::where('create_time', 'like', "{$today}%")->count(),
            'today_sales' => OrderModel::where('create_time', 'like', "{$today}%")->sum('pay_amount') ?: 0,
            'today_user' => UserModel::where('create_time', 'like', "{$today}%")->count(),
            'today_product' => ProductModel::where('status', 1)->count(),
        ];
        
        return $this->success($stats);
    }

    public function chart()
    {
        $days = $this->request->param('days', 7);
        $startDate = date('Y-m-d', strtotime("-{$days} days"));
        
        $list = OrderModel::where('status', 'in', '0,1,2,3')
            ->where('create_time', '>=', $startDate)
            ->field("from_unixtime(unix_timestamp(create_time), '%Y-%m-%d') as date, count(*) as count, sum(pay_amount) as amount")
            ->group('date')
            ->select();
        
        return $this->success($list);
    }
}