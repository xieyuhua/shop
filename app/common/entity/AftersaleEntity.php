<?php

declare(strict_types=1);

namespace app\common\entity;

use app\common\model\Order as OrderModel;
use app\common\model\OrderGoods as OrderGoodsModel;
use app\common\model\OrderAftersale as AftersaleModel;
use app\common\model\Product as ProductModel;
use app\common\model\ProductSku as ProductSkuModel;
use app\common\model\BalanceLog as BalanceLogModel;

/**
 * 售后实体 - 处理售前售后相关业务逻辑
 */
class AftersaleEntity
{
    /**
     * 申请售后
     */
    public function apply(int $userId, array $data): array
    {
        $orderId = $data['order_id'] ?? 0;
        $type = $data['type'] ?? 1; // 1=退款 2=退货退款
        $reason = $data['reason'] ?? '';
        $refundMoney = $data['refund_money'] ?? 0;
        $images = $data['images'] ?? [];

        // 验证订单
        $order = OrderModel::where('id', $orderId)
            ->where('user_id', $userId)
            ->find();
        if (!$order) {
            return ['success' => false, 'msg' => '订单不存在'];
        }

        // 验证订单状态
        if (!in_array($order->order_status, [
            OrderModel::STATUS_PENDING_DELIVERY,
            OrderModel::STATUS_PENDING_RECEIVE,
            OrderModel::STATUS_PENDING_COMMENT,
            OrderModel::STATUS_COMPLETED
        ])) {
            return ['success' => false, 'msg' => '当前订单状态不支持售后申请'];
        }

        // 检查是否有进行中的售后
        $existingAftersale = AftersaleModel::where('order_id', $orderId)
            ->whereIn('status', [AftersaleModel::STATUS_PENDING, AftersaleModel::STATUS_PROCESSING])
            ->find();
        if ($existingAftersale) {
            return ['success' => false, 'msg' => '该订单存在进行中的售后申请'];
        }

        if (empty($reason)) {
            return ['success' => false, 'msg' => '请填写售后原因'];
        }

        // 创建售后申请
        $aftersale = new AftersaleModel();
        $aftersale->order_id = $orderId;
        $aftersale->user_id = $userId;
        $aftersale->type = $type;
        $aftersale->reason = $reason;
        $aftersale->refund_money = $refundMoney > 0 ? $refundMoney : $order->pay_price;
        $aftersale->images = is_array($images) ? json_encode($images) : '';
        $aftersale->status = AftersaleModel::STATUS_PENDING;
        $aftersale->description = $data['description'] ?? '';
        $aftersale->save();

        // 更新订单状态
        if ($order->order_status == OrderModel::STATUS_PENDING_DELIVERY) {
            $order->order_status = OrderModel::STATUS_REFUNDING;
            $order->save();
        }

        return [
            'success' => true,
            'data' => [
                'aftersale_id' => $aftersale->id,
                'order_id' => $orderId,
                'refund_money' => $aftersale->refund_money,
            ],
        ];
    }

    /**
     * 取消售后申请
     */
    public function cancel(int $aftersaleId, int $userId): array
    {
        $aftersale = AftersaleModel::where('id', $aftersaleId)
            ->where('user_id', $userId)
            ->find();

        if (!$aftersale) {
            return ['success' => false, 'msg' => '售后申请不存在'];
        }

        if ($aftersale->status != AftersaleModel::STATUS_PENDING) {
            return ['success' => false, 'msg' => '当前状态不允许取消'];
        }

        $aftersale->status = AftersaleModel::STATUS_CANCELLED;
        $aftersale->cancel_time = time();
        $aftersale->save();

        // 恢复订单状态
        $order = OrderModel::find($aftersale->order_id);
        if ($order && $order->order_status == OrderModel::STATUS_REFUNDING) {
            // 根据支付状态恢复
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
     * 获取售后列表
     */
    public function getList(int $userId, int $page = 1, int $limit = 15): array
    {
        $query = AftersaleModel::with(['order', 'orderGoods'])
            ->where('user_id', $userId)
            ->order('id', 'desc');

        $total = $query->count();
        $list = $query->page($page, $limit)->select();

        return [
            'success' => true,
            'data' => [
                'list' => $list,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit),
            ],
        ];
    }

    /**
     * 获取售后详情
     */
    public function getDetail(int $aftersaleId, int $userId): array
    {
        $aftersale = AftersaleModel::with(['order', 'orderGoods'])
            ->where('id', $aftersaleId)
            ->where('user_id', $userId)
            ->find();

        if (!$aftersale) {
            return ['success' => false, 'msg' => '售后申请不存在'];
        }

        return [
            'success' => true,
            'data' => $aftersale,
        ];
    }

    /**
     * 用户邮寄商品（退货时）
     */
    public function deliver(int $aftersaleId, int $userId, array $data): array
    {
        $aftersale = AftersaleModel::where('id', $aftersaleId)
            ->where('user_id', $userId)
            ->find();

        if (!$aftersale) {
            return ['success' => false, 'msg' => '售后申请不存在'];
        }

        if ($aftersale->status != AftersaleModel::STATUS_PROCESSING) {
            return ['success' => false, 'msg' => '当前状态不允许操作'];
        }

        if ($aftersale->type != AftersaleModel::TYPE_RETURN) {
            return ['success' => false, 'msg' => '当前售后类型不需要邮寄'];
        }

        $aftersale->express_company = $data['express_company'] ?? '';
        $aftersale->express_no = $data['express_no'] ?? '';
        $aftersale->deliver_time = time();
        $aftersale->save();

        return ['success' => true];
    }

    /**
     * 商家同意售后（退款）
     */
    public function agreeRefund(int $aftersaleId, int $shopId): array
    {
        $aftersale = AftersaleModel::where('id', $aftersaleId)
            ->where('shop_id', $shopId)
            ->find();

        if (!$aftersale) {
            return ['success' => false, 'msg' => '售后申请不存在'];
        }

        if ($aftersale->status != AftersaleModel::STATUS_PENDING) {
            return ['success' => false, 'msg' => '当前状态不允许操作'];
        }

        // 退款到余额
        $user = \app\common\model\User::find($aftersale->user_id);
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
     * 商家拒绝售后
     */
    public function refuse(int $aftersaleId, int $shopId, string $reason): array
    {
        $aftersale = AftersaleModel::where('id', $aftersaleId)
            ->where('shop_id', $shopId)
            ->find();

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
     * 商家确认收货退货
     */
    public function confirmReturn(int $aftersaleId, int $shopId): array
    {
        $aftersale = AftersaleModel::where('id', $aftersaleId)
            ->where('shop_id', $shopId)
            ->find();

        if (!$aftersale) {
            return ['success' => false, 'msg' => '售后申请不存在'];
        }

        if ($aftersale->status != AftersaleModel::STATUS_PROCESSING || empty($aftersale->express_no)) {
            return ['success' => false, 'msg' => '当前状态不允许操作'];
        }

        // 退款到余额
        $user = \app\common\model\User::find($aftersale->user_id);
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
