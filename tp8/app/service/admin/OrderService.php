<?php
declare(strict_types=1);

namespace app\service\admin;

use app\entity\OrderEntity;
use app\entity\OrderItemEntity;
use think\Paginator;

class OrderService extends \app\service\Service
{
    protected $model = 'admin.Order';

    public function getList(int $page = 1, int $limit = 15, array $filter = []): Paginator
    {
        $query = OrderEntity::order('id', 'desc');
        
        if (!empty($filter['keyword'])) {
            $query->where('order_no|receiver_name|receiver_mobile', 'like', "%{$filter['keyword']}%");
        }
        if (isset($filter['status']) && $filter['status'] !== '') {
            $query->where('status', $filter['status']);
        }
        if (!empty($filter['date_range'])) {
            $dates = explode(' - ', $filter['date_range']);
            if (count($dates) == 2) {
                $query->where('create_time', 'between', [trim($dates[0]), trim($dates[1])]);
            }
        }
        
        return $query->paginate([
            'page' => $page,
            'list_rows' => $limit,
        ]);
    }

    public function getInfo(int $id): array
    {
        $order = OrderEntity::find($id);
        if (!$order) {
            return $this->error('订单不存在');
        }
        
        $items = OrderItemEntity::where('order_id', $id)->select();
        
        return $this->result([
            'order' => $order,
            'items' => $items,
        ]);
    }

    public function ship(int $id, string $expressCompany, string $expressNo): array
    {
        $order = OrderEntity::find($id);
        if (!$order) {
            return $this->error('订单不存在');
        }
        
        if (!$order->canShip()) {
            return $this->error('当前状态无法发货');
        }
        
        $order->save([
            'express_company' => $expressCompany,
            'express_no' => $expressNo,
            'ship_time' => date('Y-m-d H:i:s'),
            'status' => 2,
        ]);
        
        return $this->result(null, '发货成功');
    }

    public function cancel(int $id): array
    {
        $order = OrderEntity::find($id);
        if (!$order) {
            return $this->error('订单不存在');
        }
        
        if (!$order->canCancel()) {
            return $this->error('当前状态无法取消');
        }
        
        $order->save([
            'status' => 4,
            'cancel_time' => date('Y-m-d H:i:s'),
        ]);
        
        return $this->result(null, '取消成功');
    }

    public function refund(int $id): array
    {
        $order = OrderEntity::find($id);
        if (!$order) {
            return $this->error('订单不存在');
        }
        
        if (!$order->canRefund()) {
            return $this->error('当前状态无法退款');
        }
        
        $order->save([
            'status' => 5,
            'refund_time' => date('Y-m-d H:i:s'),
        ]);
        
        return $this->result(null, '退款成功');
    }

    public function complete(int $id): array
    {
        $order = OrderEntity::find($id);
        if (!$order) {
            return $this->error('订单不存在');
        }
        
        $order->save([
            'status' => 3,
            'complete_time' => date('Y-m-d H:i:s'),
        ]);
        
        return $this->result(null, '操作成功');
    }

    public function getStatistics(string $type = 'today'): array
    {
        $today = date('Y-m-d');
        $last7days = date('Y-m-d', strtotime('-7 days'));
        $last30days = date('Y-m-d', strtotime('-30 days'));
        
        $where = [['status', 'in', '0,1,2,3']];
        
        switch ($type) {
            case 'today':
                $where[] = ['create_time', 'like', "{$today}%"];
                break;
            case '7days':
                $where[] = ['create_time', '>=', $last7days];
                break;
            case '30days':
                $where[] = ['create_time', '>=', $last30days];
                break;
        }
        
        return [
            'order_count' => OrderEntity::where($where)->count(),
            'order_amount' => OrderEntity::where($where)->sum('pay_amount'),
            'pending_payment' => OrderEntity::where('status', 0)->count(),
            'pending_ship' => OrderEntity::where('status', 1)->count(),
            'pending_receive' => OrderEntity::where('status', 2)->count(),
        ];
    }

    public function getChartData(int $days = 7): array
    {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));
        
        $list = OrderEntity::where('status', 'in', '0,1,2,3')
            ->where('create_time', '>=', $startDate)
            ->field("from_unixtime(unix_timestamp(create_time), '%Y-%m-%d') as date, count(*) as count, sum(pay_amount) as amount")
            ->group('date')
            ->select()
            ->toArray();
        
        return $list;
    }

    public function create(array $data): array
    {
        $orderNo = $this->generateOrderNo();
        
        $data['order_no'] = $orderNo;
        $data['status'] = 0;
        
        $order = new OrderEntity();
        $order->save($data);
        
        return $this->result($order, '创建成功');
    }

    protected function generateOrderNo(): string
    {
        return date('YmdHis') . rand(1000, 9999);
    }
}