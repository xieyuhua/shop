<?php
declare(strict_types=1);

namespace app\service;

use app\model\admin\OrderModel;

class OrderService extends Service
{
    protected string $model = 'order';

    public function list(array $params = []): array
    {
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 15;
        $keyword = $params['keyword'] ?? '';
        $status = $params['status'] ?? '';

        $query = db('order')->order('id', 'desc');

        if ($keyword) {
            $query->where('order_no|receiver_name|receiver_mobile', 'like', "%{$keyword}%");
        }
        if ($status !== '') {
            $query->where('status', $status);
        }

        return $this->paginate($query, $page, $limit);
    }

    public function detail(int $id): array
    {
        $order = $this->find($id);
        if (!$order) {
            return $this->error('订单不存在');
        }

        $items = db('order_item')->where('order_id', $id)->select()->toArray();

        return $this->success([
            'order' => $order,
            'items' => $items,
        ]);
    }

    public function ship(int $id, string $express_company, string $express_no): array
    {
        $order = $this->find($id);
        if (!$order) {
            return $this->error('订单不存在');
        }

        if ($order->status != OrderModel::STATUS_PENDING) {
            return $this->error('当前状态无法发货');
        }

        $order->express_company = $express_company;
        $order->express_no = $express_no;
        $order->ship_time = date('Y-m-d H:i:s');
        $order->status = OrderModel::STATUS_SHIPPED;
        $order->save();

        return $this->success(null, '发货成功');
    }

    public function cancel(int $id): array
    {
        $order = $this->find($id);
        if (!$order) {
            return $this->error('订单不存在');
        }

        if ($order->status != OrderModel::STATUS_PENDING) {
            return $this->error('当前状态无法取消');
        }

        $order->status = OrderModel::STATUS_CANCELLED;
        $order->cancel_time = date('Y-m-d H:i:s');
        $order->save();

        return $this->success(null, '取消成功');
    }

    public function refund(int $id): array
    {
        $order = $this->find($id);
        if (!$order) {
            return $this->error('订单不存在');
        }

        if ($order->status != OrderModel::STATUS_SHIPPED) {
            return $this->error('当前状态无法退款');
        }

        $order->status = OrderModel::STATUS_REFUNDED;
        $order->refund_time = date('Y-m-d H:i:s');
        $order->save();

        return $this->success(null, '退款成功');
    }

    public function statistics(string $type = 'today'): array
    {
        $where = match ($type) {
            'today' => date('Y-m-d 00:00:00'),
            '7days' => date('Y-m-d H:i:s', strtotime('-7 days')),
            '30days' => date('Y-m-d H:i:s', strtotime('-30 days')),
            default => date('Y-m-d 00:00:00'),
        };

        $order_no = match ($type) {
            'today', '7days', '30days' => "create_time >= '{$where}'",
            default => "create_time >= '{$where}'",
        };

        $sales = db('order')
            ->whereRaw($order_no)
            ->where('status', '<>', OrderModel::STATUS_CANCELLED)
            ->sum('pay_amount');

        $count = db('order')
            ->whereRaw($order_no)
            ->where('status', '<>', OrderModel::STATUS_CANCELLED)
            ->count();

        return [
            'sales' => $sales ?: 0,
            'count' => $count ?: 0,
        ];
    }
}