<?php

declare(strict_types=1);

namespace app\common\entity;

use app\common\model\OrderAftersale as AftersaleModel;
use app\common\model\Order as OrderModel;
use app\common\model\OrderGoods as OrderGoodsModel;
use app\common\model\Product as ProductModel;
use app\common\model\ProductSku as ProductSkuModel;
use app\common\model\BalanceLog as BalanceLogModel;
use app\common\model\User as UserModel;

/**
 * 后台售后实体 - 处理售后管理业务逻辑
 */
class AdminAftersaleEntity
{
    /**
     * 获取售后列表
     */
    public function getList(array $params): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $limit = min(50, max(1, (int) ($params['limit'] ?? 15)));
        $status = $params['status'] ?? '';
        $type = $params['type'] ?? '';

        $query = AftersaleModel::with(['order', 'user', 'orderGoods'])->order('id', 'desc');

        if ($status !== '') {
            $query->where('status', (int) $status);
        }

        if ($type !== '') {
            $query->where('type', (int) $type);
        }

        $total = $query->count();
        $list = $query->page($page, $limit)->select();

        return [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ];
    }

    /**
     * 获取售后详情
     */
    public function getDetail(int $id): ?array
    {
        $aftersale = AftersaleModel::with(['order', 'user', 'orderGoods.product', 'orderGoods.sku'])->find($id);
        return $aftersale ? $aftersale->toArray() : null;
    }

    /**
     * 同意售后（退款）
     */
    public function agree(int $id): array
    {
        $aftersale = AftersaleModel::find($id);
        if (!$aftersale) {
            return ['success' => false, 'msg' => '售后申请不存在'];
        }

        if ($aftersale->status != AftersaleModel::STATUS_PENDING) {
            return ['success' => false, 'msg' => '当前状态不允许操作'];
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

        return ['success' => true];
    }

    /**
     * 拒绝售后
     */
    public function refuse(int $id, string $reason): array
    {
        if (empty($reason)) {
            return ['success' => false, 'msg' => '请填写拒绝原因'];
        }

        $aftersale = AftersaleModel::find($id);
        if (!$aftersale) {
            return ['success' => false, 'msg' => '售后申请不存在'];
        }

        if ($aftersale->status != AftersaleModel::STATUS_PENDING) {
            return ['success' => false, 'msg' => '当前状态不允许操作'];
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

        return ['success' => true];
    }

    /**
     * 确认收货退货
     */
    public function confirmReturn(int $id): array
    {
        $aftersale = AftersaleModel::find($id);
        if (!$aftersale) {
            return ['success' => false, 'msg' => '售后申请不存在'];
        }

        if ($aftersale->status != AftersaleModel::STATUS_PROCESSING || empty($aftersale->express_no)) {
            return ['success' => false, 'msg' => '当前状态不允许操作'];
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

        return ['success' => true];
    }
}
