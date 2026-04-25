<?php
declare(strict_types=1);

namespace app\api\controller;

use think\facade\Db;

class Order extends ApiController
{
    public function index()
    {
        $page = $this->request->param('page', 1);
        $limit = $this->request->param('limit', 15);
        $keyword = $this->request->param('keyword', '');
        $status = $this->request->param('status', '');

        $where = [];
        if ($keyword) {
            $where[] = ['order_no|receiver_name|receiver_mobile', 'like', "%{$keyword}%"];
        }
        if ($status !== '') {
            $where[] = ['status', '=', $status];
        }

        $list = Db::name('order')
            ->where($where)
            ->order('id', 'desc')
            ->page($page, $limit)
            ->select();

        $total = Db::name('order')->where($where)->count();

        return $this->success([
            'list' => $list,
            'total' => $total,
        ]);
    }

    public function detail()
    {
        $id = $this->request->param('id');
        
        $order = Db::name('order')->find($id);
        if (!$order) {
            return $this->error('订单不存在');
        }
        
        $items = Db::name('order_item')->where('order_id', $id)->select();
        
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

        $order = Db::name('order')->find($id);
        if ($order['status'] != 1) {
            return $this->error('当前状态无法发货');
        }

        $result = Db::name('order')->where('id', $id)->update([
            'express_company' => $express_company,
            'express_no' => $express_no,
            'ship_time' => date('Y-m-d H:i:s'),
            'status' => 2,
        ]);

        return $result !== false ? $this->success(null, '发货成功') : $this->error('发货失败');
    }

    public function cancel()
    {
        $id = $this->request->param('id');
        
        $order = Db::name('order')->find($id);
        if ($order['status'] != 0) {
            return $this->error('当前状态无法取消');
        }

        $result = Db::name('order')->where('id', $id)->update([
            'status' => 4,
            'cancel_time' => date('Y-m-d H:i:s'),
        ]);

        return $result !== false ? $this->success(null, '取消成功') : $this->error('取消失败');
    }

    public function refund()
    {
        $id = $this->request->param('id');
        
        $order = Db::name('order')->find($id);
        if (!in_array($order['status'], [1, 2])) {
            return $this->error('当前状态无法退款');
        }

        $result = Db::name('order')->where('id', $id)->update([
            'status' => 5,
            'refund_time' => date('Y-m-d H:i:s'),
        ]);

        return $result !== false ? $this->success(null, '退款成功') : $this->error('退款失败');
    }
}