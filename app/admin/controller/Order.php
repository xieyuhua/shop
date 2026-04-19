<?php

declare(strict_types=1);

namespace app\admin\controller;

use app\common\model\Order as OrderModel;
use app\common\model\OrderGoods as OrderGoodsModel;
use app\common\model\Product as ProductModel;
use app\common\model\ProductSku as ProductSkuModel;

/**
 * 后台订单管理控制器 - 仅接收参数和返回结果
 */
class Order extends BaseController
{
    /**
     * 订单列表
     */
    public function list(): \think\Response
    {
        $this->adminAuth();

        $page = (int) $this->request->get('page', 1);
        $limit = (int) $this->request->get('limit', 15);
        $status = $this->request->get('status', '');
        $keyword = $this->request->get('keyword', '');
        $dateRange = $this->request->get('date_range', '');

        $query = OrderModel::with(['user', 'shop', 'address'])
            ->order('id', 'desc');

        if ($status !== '') {
            $query->where('order_status', (int) $status);
        }

        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('order_no', 'like', '%' . $keyword . '%')
                    ->whereOr('id', $keyword);
            });
        }

        if (!empty($dateRange)) {
            $dates = explode(' - ', $dateRange);
            if (count($dates) == 2) {
                $query->whereTime('create_time', 'between', [
                    strtotime($dates[0]),
                    strtotime($dates[1] . ' 23:59:59')
                ]);
            }
        }

        $total = $query->count();
        $list = $query->page($page, $limit)->select();

        return $this->success([
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ]);
    }

    /**
     * 订单详情
     */
    public function detail(): \think\Response
    {
        $this->adminAuth();

        $id = (int) $this->request->get('id', 0);

        $order = OrderModel::with(['user', 'shop', 'address', 'orderGoods.product', 'orderGoods.sku'])
            ->find($id);

        if (!$order) {
            return $this->error('订单不存在');
        }

        return $this->success($order);
    }

    /**
     * 发货
     */
    public function delivery(): \think\Response
    {
        $this->adminAuth();

        $id = (int) $this->request->post('id', 0);
        $expressCompany = $this->request->post('express_company', '');
        $expressNo = $this->request->post('express_no', '');

        if (empty($expressCompany) || empty($expressNo)) {
            return $this->error('请填写物流信息');
        }

        $order = OrderModel::find($id);

        if (!$order) {
            return $this->error('订单不存在');
        }

        if ($order->order_status != OrderModel::STATUS_PENDING_DELIVERY) {
            return $this->error('订单状态不允许发货');
        }

        $order->express_company = $expressCompany;
        $order->express_no = $expressNo;
        $order->delivery_time = time();
        $order->order_status = OrderModel::STATUS_PENDING_RECEIVE;
        $order->save();

        return $this->success();
    }

    /**
     * 修改价格
     */
    public function updatePrice(): \think\Response
    {
        $this->adminAuth();

        $id = (int) $this->request->post('id', 0);
        $freightPrice = (float) $this->request->post('freight_price', 0);

        $order = OrderModel::find($id);

        if (!$order) {
            return $this->error('订单不存在');
        }

        if ($order->order_status != OrderModel::STATUS_PENDING_PAY) {
            return $this->error('只能对待付款订单修改价格');
        }

        $order->freight_price = $freightPrice;
        $order->pay_price = $order->total_price + $freightPrice - $order->discount_price;
        $order->save();

        return $this->success();
    }

    /**
     * 关闭订单
     */
    public function close(): \think\Response
    {
        $this->adminAuth();

        $id = (int) $this->request->post('id', 0);
        $reason = $this->request->post('reason', '管理员关闭');

        $order = OrderModel::find($id);

        if (!$order) {
            return $this->error('订单不存在');
        }

        if (!in_array($order->order_status, [
            OrderModel::STATUS_PENDING_PAY,
            OrderModel::STATUS_PENDING_DELIVERY
        ])) {
            return $this->error('当前状态不允许关闭');
        }

        $order->order_status = OrderModel::STATUS_CANCELLED;
        $order->cancel_time = time();
        $order->cancel_reason = $reason;
        $order->save();

        // 恢复库存
        if ($order->pay_status == OrderModel::PAY_STATUS_PAID) {
            // 退款处理（简化，实际应退回余额）
        }

        foreach ($order->orderGoods as $goods) {
            if ($goods->sku_id > 0) {
                $sku = ProductSkuModel::find($goods->sku_id);
                if ($sku) {
                    $sku->stock = $sku->stock + $goods->num;
                    $sku->save();
                }
            } else {
                $product = ProductModel::find($goods->product_id);
                if ($product) {
                    $product->stock = $product->stock + $goods->num;
                    $product->save();
                }
            }
        }

        return $this->success();
    }

    /**
     * 导出订单
     */
    public function export(): \think\Response
    {
        $this->adminAuth();

        // 简化实现，实际可使用 fast-excel 等库
        return $this->success([], '导出功能开发中');
    }
}
