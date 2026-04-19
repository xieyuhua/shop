<?php

declare(strict_types=1);

namespace app\admin\controller;

use app\common\model\OrderAftersale as AftersaleModel;
use app\common\model\Order as OrderModel;
use app\common\model\OrderGoods as OrderGoodsModel;
use app\common\model\Product as ProductModel;
use app\common\model\ProductSku as ProductSkuModel;
use app\common\model\BalanceLog as BalanceLogModel;
use app\common\model\User as UserModel;

/**
 * 后台售后管理控制器 - 仅接收参数和返回结果
 */
class Aftersale extends BaseController
{
    /**
     * 售后列表
     */
    public function list(): \think\Response
    {
        $this->adminAuth();

        $page = (int) $this->request->get('page', 1);
        $limit = (int) $this->request->get('limit', 15);
        $status = $this->request->get('status', '');
        $type = $this->request->get('type', '');

        $query = AftersaleModel::with(['order', 'user', 'orderGoods'])
            ->order('id', 'desc');

        if ($status !== '') {
            $query->where('status', (int) $status);
        }

        if ($type !== '') {
            $query->where('type', (int) $type);
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
     * 售后详情
     */
    public function detail(): \think\Response
    {
        $this->adminAuth();

        $id = (int) $this->request->get('id', 0);

        $aftersale = AftersaleModel::with(['order', 'user', 'orderGoods.product', 'orderGoods.sku'])
            ->find($id);

        if (!$aftersale) {
            return $this->error('售后申请不存在');
        }

        return $this->success($aftersale);
    }

    /**
     * 同意售后（退款）
     */
    public function agree(): \think\Response
    {
        $this->adminAuth();

        $id = (int) $this->request->post('id', 0);

        $aftersale = AftersaleModel::find($id);

        if (!$aftersale) {
            return $this->error('售后申请不存在');
        }

        if ($aftersale->status != AftersaleModel::STATUS_PENDING) {
            return $this->error('当前状态不允许操作');
        }

        // 退款到余额
        $user = UserModel::find($aftersale->user_id);
        if ($user) {
            $user->balance = $user->balance + $aftersale->refund_money;
            $user->save();

            $balanceLog = new BalanceLogModel();
            $balanceLog->user_id = $aftersale->user_id;
            $balanceLog->change_type = BalanceLogModel::TYPE_INCOME;
            $balanceLog->balance = $aftersale->refund_money;
            $balanceLog->description = '售后退款：' . $aftersale->reason;
            $balanceLog->source_type = 'aftersale';
            $balanceLog->source_id = $aftersale->id;
            $balanceLog->create_time = time();
            $balanceLog->save();
        }

        $aftersale->status = AftersaleModel::STATUS_AGREE;
        $aftersale->refund_time = time();
        $aftersale->save();

        // 更新订单状态
        $order = OrderModel::find($aftersale->order_id);
        if ($order) {
            if ($aftersale->type == AftersaleModel::TYPE_REFUND) {
                $order->order_status = OrderModel::STATUS_REFUNDED;
            }
            $order->save();
        }

        return $this->success();
    }

    /**
     * 拒绝售后
     */
    public function refuse(): \think\Response
    {
        $this->adminAuth();

        $id = (int) $this->request->post('id', 0);
        $reason = $this->request->post('reason', '');

        if (empty($reason)) {
            return $this->error('请填写拒绝原因');
        }

        $aftersale = AftersaleModel::find($id);

        if (!$aftersale) {
            return $this->error('售后申请不存在');
        }

        if ($aftersale->status != AftersaleModel::STATUS_PENDING) {
            return $this->error('当前状态不允许操作');
        }

        $aftersale->status = AftersaleModel::STATUS_REFUSE;
        $aftersale->refuse_reason = $reason;
        $aftersale->refuse_time = time();
        $aftersale->save();

        // 恢复订单状态
        $order = OrderModel::find($aftersale->order_id);
        if ($order && $order->order_status == OrderModel::STATUS_REFUNDING) {
            if ($order->pay_status == OrderModel::PAY_STATUS_PAID) {
                $order->order_status = OrderModel::STATUS_PENDING_DELIVERY;
            } else {
                $order->order_status = OrderModel::STATUS_PENDING_PAY;
            }
            $order->save();
        }

        return $this->success();
    }

    /**
     * 确认收货退货
     */
    public function confirmReturn(): \think\Response
    {
        $this->adminAuth();

        $id = (int) $this->request->post('id', 0);

        $aftersale = AftersaleModel::find($id);

        if (!$aftersale) {
            return $this->error('售后申请不存在');
        }

        if ($aftersale->status != AftersaleModel::STATUS_PROCESSING || empty($aftersale->express_no)) {
            return $this->error('当前状态不允许操作');
        }

        // 退款到余额
        $user = UserModel::find($aftersale->user_id);
        if ($user) {
            $user->balance = $user->balance + $aftersale->refund_money;
            $user->save();

            $balanceLog = new BalanceLogModel();
            $balanceLog->user_id = $aftersale->user_id;
            $balanceLog->change_type = BalanceLogModel::TYPE_INCOME;
            $balanceLog->balance = $aftersale->refund_money;
            $balanceLog->description = '售后退货退款：' . $aftersale->reason;
            $balanceLog->source_type = 'aftersale';
            $balanceLog->source_id = $aftersale->id;
            $balanceLog->create_time = time();
            $balanceLog->save();
        }

        $aftersale->status = AftersaleModel::STATUS_AGREE;
        $aftersale->refund_time = time();
        $aftersale->save();

        // 恢复库存
        $orderGoods = OrderGoodsModel::where('order_id', $aftersale->order_id)->find();
        if ($orderGoods) {
            if ($orderGoods->sku_id > 0) {
                $sku = ProductSkuModel::find($orderGoods->sku_id);
                if ($sku) {
                    $sku->stock = $sku->stock + $orderGoods->num;
                    $sku->save();
                }
            } else {
                $product = ProductModel::find($orderGoods->product_id);
                if ($product) {
                    $product->stock = $product->stock + $orderGoods->num;
                    $product->save();
                }
            }
        }

        // 更新订单状态
        $order = OrderModel::find($aftersale->order_id);
        if ($order) {
            $order->order_status = OrderModel::STATUS_REFUNDED;
            $order->save();
        }

        return $this->success();
    }
}
