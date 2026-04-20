<?php

declare(strict_types=1);

namespace app\common\entity;

use app\common\model\User;
use app\common\model\Order;
use app\common\model\Shop;
use app\common\model\Product;
use app\common\model\OrderAftersale;

/**
 * 仪表盘实体
 */
class DashboardEntity
{
    /**
     * 获取统计数据
     */
    public function getStatistics(): array
    {
        $today = strtotime('today');
        $yesterday = strtotime('yesterday');

        // 用户统计
        $totalUsers = User::count();
        $todayUsers = User::whereTime('create_time', 'today')->count();
        $yesterdayUsers = User::whereTime('create_time', 'yesterday')->count();

        // 订单统计
        $totalOrders = Order::count();
        $todayOrders = Order::whereTime('create_time', 'today')->count();
        $yesterdayOrders = Order::whereTime('create_time', 'yesterday')->count();

        // 今日销售额
        $todaySales = Order::where('pay_status', Order::PAY_STATUS_PAID)
            ->whereTime('pay_time', 'today')
            ->sum('pay_price');

        // 昨日销售额
        $yesterdaySales = Order::where('pay_status', Order::PAY_STATUS_PAID)
            ->whereBetween('pay_time', [$yesterday, $today - 1])
            ->sum('pay_price');

        // 店铺统计
        $totalShops = Shop::count();
        $pendingShops = Shop::where('status', Shop::STATUS_PENDING)->count();

        // 商品统计
        $totalProducts = Product::count();
        $offShelfProducts = Product::where('is_on_sale', 0)->count();

        // 订单状态分布
        $orderStatus = [
            'pending_pay' => Order::where('order_status', Order::STATUS_PENDING_PAY)->count(),
            'pending_delivery' => Order::where('order_status', Order::STATUS_PENDING_DELIVERY)->count(),
            'pending_receive' => Order::where('order_status', Order::STATUS_PENDING_RECEIVE)->count(),
            'pending_comment' => Order::where('order_status', Order::STATUS_PENDING_COMMENT)->count(),
        ];

        // 近7天销售趋势
        $salesTrend = $this->getSalesTrend();

        // 近7天新增用户趋势
        $userTrend = $this->getUserTrend();

        // 最近订单
        $recentOrders = Order::with(['user', 'shop'])
            ->order('id', 'desc')
            ->limit(10)
            ->select();

        return [
            'user' => [
                'total' => $totalUsers,
                'today' => $todayUsers,
                'yesterday' => $yesterdayUsers,
                'growth' => $yesterdayUsers > 0 ? round(($todayUsers - $yesterdayUsers) / $yesterdayUsers * 100, 2) : 0,
            ],
            'order' => [
                'total' => $totalOrders,
                'today' => $todayOrders,
                'yesterday' => $yesterdayOrders,
                'growth' => $yesterdayOrders > 0 ? round(($todayOrders - $yesterdayOrders) / $yesterdayOrders * 100, 2) : 0,
            ],
            'sales' => [
                'today' => round($todaySales, 2),
                'yesterday' => round($yesterdaySales, 2),
                'growth' => $yesterdaySales > 0 ? round(($todaySales - $yesterdaySales) / $yesterdaySales * 100, 2) : 0,
            ],
            'shop' => [
                'total' => $totalShops,
                'pending' => $pendingShops,
            ],
            'product' => [
                'total' => $totalProducts,
                'off_shelf' => $offShelfProducts,
            ],
            'order_status' => $orderStatus,
            'sales_trend' => $salesTrend,
            'user_trend' => $userTrend,
            'recent_orders' => $recentOrders,
        ];
    }

    /**
     * 快捷操作统计
     */
    public function getQuickStats(): array
    {
        $pendingOrders = Order::where('order_status', Order::STATUS_PENDING_DELIVERY)->count();
        $pendingAftersales = OrderAftersale::where('status', OrderAftersale::STATUS_PENDING)->count();
        $pendingShops = Shop::where('status', Shop::STATUS_PENDING)->count();
        $pendingProducts = Product::where('status', 0)->count();

        return [
            'pending_orders' => $pendingOrders,
            'pending_aftersales' => $pendingAftersales,
            'pending_shops' => $pendingShops,
            'pending_products' => $pendingProducts,
        ];
    }

    /**
     * 获取近7天销售趋势
     */
    private function getSalesTrend(): array
    {
        $trend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $start = strtotime($date);
            $end = strtotime($date . ' 23:59:59');

            $sales = Order::where('pay_status', Order::PAY_STATUS_PAID)
                ->whereBetween('pay_time', [$start, $end])
                ->sum('pay_price');

            $orderCount = Order::where('pay_status', Order::PAY_STATUS_PAID)
                ->whereBetween('pay_time', [$start, $end])
                ->count();

            $trend[] = [
                'date' => $date,
                'sales' => round($sales, 2),
                'orders' => $orderCount,
            ];
        }
        return $trend;
    }

    /**
     * 获取近7天用户趋势
     */
    private function getUserTrend(): array
    {
        $trend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $userCount = User::whereTime('create_time', $date)->count();

            $trend[] = [
                'date' => $date,
                'users' => $userCount,
            ];
        }
        return $trend;
    }
}
