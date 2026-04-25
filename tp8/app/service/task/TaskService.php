<?php
declare(strict_types=1);

namespace app\service;

use think\console\Command;
use think\console\input\Option;
use think\console\input\Argument;

class TaskCommand extends Command
{
    protected $signature = 'task {name= : 任务名称} {action=run : 执行动作}';
    protected $description = '定时任务';

    protected function configure()
    {
        $this->configure = [
            'name' => Argument::REQUIRED,
            'action' => Argument::OPTIONAL
        ];
    }

    protected function execute()
    {
        $name = input('name');
        $action = input('action', 'run');

        $map = [
            'order' => OrderTaskService::class,
            'product' => ProductTaskService::class,
            'statistics' => StatisticsTaskService::class
        ];

        if (!isset($map[$name])) {
            $this->error("任务 [{$name}] 不存在");
            return;
        }

        $service = new $map[$name];
        
        if (!method_exists($service, $action)) {
            $this->error("方法 [{$action}] 不存在");
            return;
        }

        $startTime = microtime(true);
        $this->info("开始执行任务: {$name}");

        try {
            $service->$action();
            $cost = round(microtime(true) - $startTime, 3);
            $this->info("任务执行成功，耗时: {$cost}s");
        } catch (\Exception $e) {
            $this->error("任务执行失败: " . $e->getMessage());
        }
    }
}

class OrderTaskService
{
    public function run(): void
    {
        $expire = 30 * 60;
        $orders = \think\facade\Db::name('order')
            ->where('status', 0)
            ->where('create_time', '<', date('Y-m-d H:i:s', time() - $expire))
            ->select();

        foreach ($orders as $order) {
            \think\facade\Db::name('order')->where('id', $order['id'])->update([
                'status' => 4,
                'cancel_time' => date('Y-m-d H:i:s'),
                'cancel_reason' => '超时未支付'
            ]);
        }

        echo "已取消 " . count($orders) . " 个超时订单\n";
    }

    public function statistics(): void
    {
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        
        $orderCount = \think\facade\Db::name('order')
            ->where('status', 3)
            ->where('create_time', 'like', "{$yesterday}%")
            ->count();

        $orderAmount = \think\facade\Db::name('order')
            ->where('status', 3)
            ->where('create_time', 'like', "{$yesterday}%")
            ->sum('pay_amount');

        echo "昨日订单: {$orderCount}, 销售额: {$orderAmount}\n";
    }
}

class ProductTaskService
{
    public function run(): void
    {
        $products = \think\facade\Db::name('product')
            ->where('status', 1)
            ->where('stock', '<', 10)
            ->select();

        foreach ($products as $product) {
            echo "商品 [{$product['name']}] 库存不足: {$product['stock']}\n";
        }
    }
}

class StatisticsTaskService
{
    public function run(): void
    {
        CacheService::forget('stats:today');
        CacheService::forget('stats:yesterday');
        
        echo "缓存已刷新\n";
    }
}