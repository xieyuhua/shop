<?php

declare(strict_types=1);

namespace app\common\entity;

use app\common\model\Order;
use app\common\model\OrderGoods;
use app\common\model\OrderAftersale;
use app\common\model\Product;
use app\common\model\ProductSku;
use app\common\model\BalanceLog;
use app\common\model\User;

/**
 * 售后实体
 */
class AftersaleEntity extends BaseEntity
{
    protected $table = 'order_aftersale';

    protected $type = [
        'refund_money' => 'float',
    ];

    // ========== 业务逻辑 ==========

    /**
     * 申请售后
     */
    public function apply(int $userId, array $data): array
    {
        $orderId = $data['order_id'] ?? 0;
        $type = $data['type'] ?? 1;
        $reason = $data['reason'] ?? '';
        $refundMoney = $data['refund_money'] ?? 0;
        $images = $data['images'] ?? [];

        $order = Order::where('id', $orderId)
            ->where('user_id', $userId)
            ->find();
        if (!$order) {
            return ['success' => false, 'msg' => '订单不存在'];
        }

        if (!in_array($order->order_status, [
            Order::STATUS_PENDING_DELIVERY,
            Order::STATUS_PENDING_RECEIVE,
            Order::STATUS_PENDING_COMMENT,
            Order::STATUS_COMPLETED
        ])) {
            return ['success' => false, 'msg' => '当前订单状态不支持售后申请'];
        }

        $existingAftersale = self::where('order_id', $orderId)
            ->whereIn('status', [OrderAftersale::STATUS_PENDING, OrderAftersale::STATUS_PROCESSING])
            ->find();
        if ($existingAftersale) {
            return ['success' => false, 'msg' => '该订单存在进行中的售后申请'];
        }

        if (empty($reason)) {
            return ['success' => false, 'msg' => '请填写售后原因'];
        }

        $aftersale = new self();
        $aftersale->order_id = $orderId;
        $aftersale->user_id = $userId;
        $aftersale->type = $type;
        $aftersale->reason = $reason;
        $aftersale->refund_money = $refundMoney > 0 ? $refundMoney : $order->pay_price;
        $aftersale->images = is_array($images) ? json_encode($images) : '';
        $aftersale->status = OrderAftersale::STATUS_PENDING;
        $aftersale->description = $data['description'] ?? '';
        $aftersale->save();

        if ($order->order_status == Order::STATUS_PENDING_DELIVERY) {
            $order->order_status = Order::STATUS_REFUNDING;
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
        $aftersale = self::where('id', $aftersaleId)
            ->where('user_id', $userId)
            ->find();

        if (!$aftersale) {
            return ['success' => false, 'msg' => '售后申请不存在'];
        }

        if ($aftersale->status != OrderAftersale::STATUS_PENDING) {
            return ['success' => false, 'msg' => '当前状态不允许取消'];
        }

        $aftersale->status = OrderAftersale::STATUS_CANCELLED;
        $aftersale->cancel_time = time();
        $aftersale->save();

        $order = Order::find($aftersale->order_id);
        if ($order && $order->order_status == Order::STATUS_REFUNDING) {
            if ($order->pay_status == Order::PAY_STATUS_PAID) {
                $order->order_status = Order::STATUS_PENDING_DELIVERY;
            } else {
                $order->order_status = Order::STATUS_PENDING_PAY;
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
        $query = self::with(['order', 'orderGoods'])
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
        $aftersale = self::with(['order', 'orderGoods'])
            ->where('id', $aftersaleId)
            ->where('user_id', $userId)
            ->find();

        if (!$aftersale) {
            return ['success' => false, 'msg' => '售后申请不存在'];
        }

        return ['success' => true, 'data' => $aftersale];
    }

    /**
     * 用户邮寄商品
     */
    public function deliver(int $aftersaleId, int $userId, array $data): array
    {
        $aftersale = self::where('id', $aftersaleId)
            ->where('user_id', $userId)
            ->find();

        if (!$aftersale) {
            return ['success' => false, 'msg' => '售后申请不存在'];
        }

        if ($aftersale->status != OrderAftersale::STATUS_PROCESSING) {
            return ['success' => false, 'msg' => '当前状态不允许操作'];
        }

        if ($aftersale->type != OrderAftersale::TYPE_RETURN) {
            return ['success' => false, 'msg' => '当前售后类型不需要邮寄'];
        }

        $aftersale->express_company = $data['express_company'] ?? '';
        $aftersale->express_no = $data['express_no'] ?? '';
        $aftersale->deliver_time = time();
        $aftersale->save();

        return ['success' => true];
    }

    /**
     * 商家同意售后
     */
    public function agreeRefund(int $aftersaleId, int $shopId): array
    {
        $aftersale = self::where('id', $aftersaleId)
            ->where('shop_id', $shopId)
            ->find();

        if (!$aftersale) {
            return ['success' => false, 'msg' => '售后申请不存在'];
        }

        if ($aftersale->status != OrderAftersale::STATUS_PENDING) {
            return ['success' => false, 'msg' => '当前状态不允许操作'];
        }

        $user = User::find($aftersale->user_id);
        if ($user) {
            $user->balance = $user->balance + $aftersale->refund_money;
            $user->save();

            $balanceLog = new BalanceLog();
            $balanceLog->user_id = $aftersale->user_id;
            $balanceLog->change_type = BalanceLog::TYPE_INCOME;
            $balanceLog->balance = $aftersale->refund_money;
            $balanceLog->description = '售后退款：' . $aftersale->reason;
            $balanceLog->source_type = 'aftersale';
            $balanceLog->source_id = $aftersale->id;
            $balanceLog->save();
        }

        $aftersale->status = OrderAftersale::STATUS_AGREE;
        $aftersale->refund_time = time();
        $aftersale->save();

        $order = Order::find($aftersale->order_id);
        if ($order) {
            if ($aftersale->type == OrderAftersale::TYPE_REFUND) {
                $order->order_status = Order::STATUS_REFUNDED;
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
        $aftersale = self::where('id', $aftersaleId)
            ->where('shop_id', $shopId)
            ->find();

        if (!$aftersale) {
            return ['success' => false, 'msg' => '售后申请不存在'];
        }

        if ($aftersale->status != OrderAftersale::STATUS_PENDING) {
            return ['success' => false, 'msg' => '当前状态不允许操作'];
        }

        $aftersale->status = OrderAftersale::STATUS_REFUSE;
        $aftersale->refuse_reason = $reason;
        $aftersale->refuse_time = time();
        $aftersale->save();

        $order = Order::find($aftersale->order_id);
        if ($order && $order->order_status == Order::STATUS_REFUNDING) {
            if ($order->pay_status == Order::PAY_STATUS_PAID) {
                $order->order_status = Order::STATUS_PENDING_DELIVERY;
            } else {
                $order->order_status = Order::STATUS_PENDING_PAY;
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
        $aftersale = self::where('id', $aftersaleId)
            ->where('shop_id', $shopId)
            ->find();

        if (!$aftersale) {
            return ['success' => false, 'msg' => '售后申请不存在'];
        }

        if ($aftersale->status != OrderAftersale::STATUS_PROCESSING || empty($aftersale->express_no)) {
            return ['success' => false, 'msg' => '当前状态不允许操作'];
        }

        $user = User::find($aftersale->user_id);
        if ($user) {
            $user->balance = $user->balance + $aftersale->refund_money;
            $user->save();

            $balanceLog = new BalanceLog();
            $balanceLog->user_id = $aftersale->user_id;
            $balanceLog->change_type = BalanceLog::TYPE_INCOME;
            $balanceLog->balance = $aftersale->refund_money;
            $balanceLog->description = '售后退货退款：' . $aftersale->reason;
            $balanceLog->source_type = 'aftersale';
            $balanceLog->source_id = $aftersale->id;
            $balanceLog->save();
        }

        $aftersale->status = OrderAftersale::STATUS_AGREE;
        $aftersale->refund_time = time();
        $aftersale->save();

        $orderGoods = OrderGoods::where('order_id', $aftersale->order_id)->find();
        if ($orderGoods) {
            if ($orderGoods->sku_id > 0) {
                $sku = ProductSku::find($orderGoods->sku_id);
                if ($sku) {
                    $sku->stock = $sku->stock + $orderGoods->num;
                    $sku->save();
                }
            } else {
                $product = Product::find($orderGoods->product_id);
                if ($product) {
                    $product->stock = $product->stock + $orderGoods->num;
                    $product->save();
                }
            }
        }

        $order = Order::find($aftersale->order_id);
        if ($order) {
            $order->order_status = Order::STATUS_REFUNDED;
            $order->save();
        }

        return ['success' => true];
    }
}
