<?php
declare(strict_types=1);

namespace app\api\controller;

use app\model\admin\OrderModel;
use app\model\admin\OrderItemModel;

class Order extends ApiController
{
    public function index()
    {
        $page = $this->request->param('page', 1);
        $limit = $this->request->param('limit', 15);
        $keyword = $this->request->param('keyword', '');
        $status = $this->request->param('status', '');

        $query = OrderModel::order('id', 'desc');
        
        if ($keyword) {
            $query->where('order_no|receiver_name|receiver_mobile', 'like', "%{$keyword}%");
        }
        if ($status !== '') {
            $query->where('status', $status);
        }

        $list = $query->page($page, $limit)->select();
        $total = $query->count();

        return $this->success([
            'list' => $list,
            'total' => $total,
        ]);
    }

    public function detail()
    {
        $id = $this->request->param('id');
        
        $order = OrderModel::find($id);
        if (!$order) {
            return $this->error('订单不存在');
        }
        
        $items = $order->items()->select();
        
        return $this->success([
            'order' => $order,
            'items' => $items,
        ]);
    }

    public function ship()
    {
        $id = $this->request->post('id');
        $express_company = $this->request->post('express_company');
        $express_no = $this->request->post('express_no');

        $order = OrderModel::find($id);
        if (!$order) {
            return $this->error('订单不存在');
        }

        if (!$order->canShip()) {
            return $this->error('当前状态无法发货');
        }

        $order->express_company = $express_company;
        $order->express_no = $express_no;
        $order->ship_time = date('Y-m-d H:i:s');
        $order->status = OrderModel::STATUS_SHIPPED;
        $order->save();

        return $this->success(null, '发货成功');
    }

    public function cancel()
    {
        $id = $this->request->param('id');
        
        $order = OrderModel::find($id);
        if (!$order) {
            return $this->error('订单不存在');
        }

        if (!$order->canCancel()) {
            return $this->error('当前状态无法取消');
        }

        $order->status = OrderModel::STATUS_CANCELLED;
        $order->cancel_time = date('Y-m-d H:i:s');
        $order->save();

        return $this->success(null, '取消成功');
    }

    public function refund()
    {
        $id = $this->request->param('id');
        
        $order = OrderModel::find($id);
        if (!$order) {
            return $this->error('订单不存在');
        }

        if (!$order->canRefund()) {
            return $this->error('当前状态无法退款');
        }

        $order->status = OrderModel::STATUS_REFUNDED;
        $order->refund_time = date('Y-m-d H:i:s');
        $order->save();

        return $this->success(null, '退款成功');
    }
}