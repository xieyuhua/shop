<?php

declare(strict_types=1);

namespace app\admin\controller;

use app\common\model\User as UserModel;
use app\common\model\Order as OrderModel;
use app\common\model\Shop as ShopModel;
use app\common\model\Product as ProductModel;

/**
 * 后台仪表盘控制器 - 仅接收参数和返回结果
 */
class Dashboard extends BaseController
{
    /**
     * 获取统计数据
     */
    public function statistics(): \think\Response
    {
        $this->adminAuth();

        $today = strtotime('today');
        $yesterday = strtotime('yesterday');

        // 用户统计
        $totalUsers = UserModel::count();
        $todayUsers = UserModel::whereTime('create_time', 'today')->count();
        $yesterdayUsers = UserModel::whereTime('create_time', 'yesterday')->count();

        // 订单统计
        $totalOrders = OrderModel::count();
        $todayOrders = OrderModel::whereTime('create_time', 'today')->count();
        $yesterdayOrders = OrderModel::whereTime('create_time', 'yesterday')->count();

        // 今日销售额
        $todaySales = OrderModel::where('pay_status', OrderModel::PAY_STATUS_PAID)
            ->whereTime('pay_time', 'today')
            ->sum('pay_price');

        // 昨日销售额
        $yesterdaySales = OrderModel::where('pay_status', OrderModel::PAY_STATUS_PAID)
            ->whereBetween('pay_time', [$yesterday, $today - 1])
            ->sum('pay_price');

        // 店铺统计
        $totalShops = ShopModel::count();
        $pendingShops = ShopModel::where('status', ShopModel::STATUS_PENDING)->count();

        // 商品统计
        $totalProducts = ProductModel::count();
        $offShelfProducts = ProductModel::where('is_on_sale', 0)->count();

        // 订单状态分布
        $orderStatus = [
            'pending_pay' => OrderModel::where('order_status', OrderModel::STATUS_PENDING_PAY)->count(),
            'pending_delivery' => OrderModel::where('order_status', OrderModel::STATUS_PENDING_DELIVERY)->count(),
            'pending_receive' => OrderModel::where('order_status', OrderModel::STATUS_PENDING_RECEIVE)->count(),
            'pending_comment' => OrderModel::where('order_status', OrderModel::STATUS_PENDING_COMMENT)->count(),
        ];

        // 近7天销售趋势
        $salesTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $start = strtotime($date);
            $end = strtotime($date . ' 23:59:59');

            $sales = OrderModel::where('pay_status', OrderModel::PAY_STATUS_PAID)
                ->whereBetween('pay_time', [$start, $end])
                ->sum('pay_price');

            $orderCount = OrderModel::where('pay_status', OrderModel::PAY_STATUS_PAID)
                ->whereBetween('pay_time', [$start, $end])
                ->count();

            $salesTrend[] = [
                'date' => $date,
                'sales' => round($sales, 2),
                'orders' => $orderCount,
            ];
        }

        // 近7天新增用户趋势
        $userTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));

            $userCount = UserModel::whereTime('create_time', $date)->count();

            $userTrend[] = [
                'date' => $date,
                'users' => $userCount,
            ];
        }

        // 最近订单
        $recentOrders = OrderModel::with(['user', 'shop'])
            ->order('id', 'desc')
            ->limit(10)
            ->select();

        return $this->success([
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
        ]);
    }

    /**
     * 快捷操作统计
     */
    public function quickStats(): \think\Response
    {
        $this->adminAuth();

        $pendingOrders = OrderModel::where('order_status', OrderModel::STATUS_PENDING_DELIVERY)->count();
        $pendingAftersales = \app\common\model\OrderAftersale::where('status', \app\common\model\OrderAftersale::STATUS_PENDING)->count();
        $pendingShops = ShopModel::where('status', ShopModel::STATUS_PENDING)->count();
        $pendingProducts = ProductModel::where('status', 0)->count();

        return $this->success([
            'pending_orders' => $pendingOrders,
            'pending_aftersales' => $pendingAftersales,
            'pending_shops' => $pendingShops,
            'pending_products' => $pendingProducts,
        ]);
    }
}
