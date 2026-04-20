<?php

declare(strict_types=1);

namespace app\common\entity;

use app\common\model\OrderAftersale;
use app\common\model\Order;
use app\common\model\OrderGoods;
use app\common\model\Product;
use app\common\model\ProductSku;
use app\common\model\BalanceLog;
use app\common\model\User;

/**
 * 后台售后实体
 */
class AdminAftersaleEntity extends BaseEntity
{
    protected $table = 'order_aftersale';

    protected $type = [
        'refund_money' => 'float',
    ];

    // ========== 业务逻辑 ==========

    /**
     * 获取售后列表
     */
    public function getList(array $params): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $limit = min(50, max(1, (int) ($params['limit'] ?? 15)));
        $status = $params['status'] ?? '';
        $type = $params['type'] ?? '';

        $query = self::with(['order', 'user', 'orderGoods'])->order('id', 'desc');

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
        $aftersale = self::with(['order', 'user', 'orderGoods.product', 'orderGoods.sku'])->find($id);
        return $aftersale ? $aftersale->toArray() : null;
    }

    /**
     * 同意售后
     */
    public function agree(int $id): array
    {
        $aftersale = self::find($id);
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
     * 拒绝售后
     */
    public function refuse(int $id, string $reason): array
    {
        if (empty($reason)) {
            return ['success' => false, 'msg' => '请填写拒绝原因'];
        }

        $aftersale = self::find($id);
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
     * 确认收货退货
     */
    public function confirmReturn(int $id): array
    {
        $aftersale = self::find($id);
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
