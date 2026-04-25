<?php
declare(strict_types=1);

namespace app\api\controller;

use think\facade\Db;

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
            'order_count' => Db::name('order')->where($orderWhere)->count(),
            'order_amount' => Db::name('order')->where($orderWhere)->sum('pay_amount') ?: 0,
            'user_count' => Db::name('user')->where($userWhere)->count(),
            'product_count' => Db::name('product')->where('status', 1)->count(),
            'pending_payment' => Db::name('order')->where('status', 0)->count(),
            'pending_ship' => Db::name('order')->where('status', 1)->count(),
            'pending_receive' => Db::name('order')->where('status', 2)->count(),
            'today_order' => Db::name('order')->where('create_time', 'like', "{$today}%")->count(),
            'today_sales' => Db::name('order')->where('create_time', 'like', "{$today}%")->sum('pay_amount') ?: 0,
            'today_user' => Db::name('user')->where('create_time', 'like', "{$today}%")->count(),
            'today_product' => Db::name('product')->where('status', 1)->count(),
        ];
        
        return $this->success($stats);
    }

    public function chart()
    {
        $days = $this->request->param('days', 7);
        $startDate = date('Y-m-d', strtotime("-{$days} days"));
        
        $list = Db::name('order')
            ->where('status', 'in', '0,1,2,3')
            ->where('create_time', '>=', $startDate)
            ->field("from_unixtime(unix_timestamp(create_time), '%Y-%m-%d') as date, count(*) as count, sum(pay_amount) as amount")
            ->group('date')
            ->select();
        
        return $this->success($list);
    }
}