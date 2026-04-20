<?php

declare(strict_types=1);

namespace app\common\entity;

use app\common\model\Order;
use app\common\model\ProductSku;
use app\common\model\Product;
use app\common\model\User;
use app\common\model\Shop;
use app\common\model\UserAddress;
use app\common\model\OrderGoods;
use think\model\concern\SoftDelete;

/**
 * 后台订单实体
 */
class AdminOrderEntity extends BaseEntity
{
    use SoftDelete;

    protected $table = 'order';
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;

    protected $type = [
        'total_price' => 'float',
        'pay_price' => 'float',
        'freight_price' => 'float',
        'discount_price' => 'float',
    ];

    // ========== 业务逻辑 ==========

    /**
     * 获取订单列表
     */
    public function getList(array $params): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $limit = min(50, max(1, (int) ($params['limit'] ?? 15)));
        $status = $params['status'] ?? '';
        $keyword = $params['keyword'] ?? '';
        $dateRange = $params['date_range'] ?? '';

        $query = self::with(['user', 'shop', 'address'])->order('id', 'desc');

        if ($status !== '') {
            $query->where('order_status', (int) $status);
        }

        if (!empty($keyword)) {
            $keyword = addcslashes($keyword, '%_');
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

        return [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ];
    }

    /**
     * 获取订单详情
     */
    public function getDetail(int $id): ?array
    {
        $order = self::with(['user', 'shop', 'address', 'orderGoods.product', 'orderGoods.sku'])
            ->find($id);

        return $order ? $order->toArray() : null;
    }

    /**
     * 订单发货
     */
    public function delivery(int $id, string $expressCompany, string $expressNo): array
    {
        if (empty($expressCompany) || empty($expressNo)) {
            return ['success' => false, 'msg' => '请填写物流信息'];
        }

        $order = self::find($id);
        if (!$order) {
            return ['success' => false, 'msg' => '订单不存在'];
        }

        if ($order->order_status != Order::STATUS_PENDING_DELIVERY) {
            return ['success' => false, 'msg' => '订单状态不允许发货'];
        }

        $order->express_company = $expressCompany;
        $order->express_no = $expressNo;
        $order->delivery_time = time();
        $order->order_status = Order::STATUS_PENDING_RECEIVE;
        $order->save();

        return ['success' => true];
    }

    /**
     * 修改订单价格
     */
    public function updatePrice(int $id, float $freightPrice): array
    {
        $order = self::find($id);
        if (!$order) {
            return ['success' => false, 'msg' => '订单不存在'];
        }

        if ($order->order_status != Order::STATUS_PENDING_PAY) {
            return ['success' => false, 'msg' => '只能对待付款订单修改价格'];
        }

        $order->freight_price = $freightPrice;
        $order->pay_price = $order->total_price + $freightPrice - $order->discount_price;
        $order->save();

        return ['success' => true];
    }

    /**
     * 关闭订单
     */
    public function close(int $id, string $reason = '管理员关闭', int $adminId = 0): array
    {
        $order = self::find($id);
        if (!$order) {
            return ['success' => false, 'msg' => '订单不存在'];
        }

        $allowedStatus = [
            Order::STATUS_PENDING_PAY,
            Order::STATUS_PENDING_DELIVERY
        ];

        if (!in_array($order->order_status, $allowedStatus)) {
            return ['success' => false, 'msg' => '当前状态不允许关闭'];
        }

        $order->order_status = Order::STATUS_CANCELLED;
        $order->cancel_time = time();
        $order->cancel_reason = $reason;
        $order->save();

        if ($order->pay_status == Order::PAY_STATUS_PAID) {
            $this->restoreStock($order);
        }

        return ['success' => true];
    }

    private function restoreStock(Order $order): void
    {
        foreach ($order->orderGoods as $goods) {
            if ($goods->sku_id > 0) {
                $sku = ProductSku::find($goods->sku_id);
                if ($sku) {
                    $sku->stock = $sku->stock + $goods->num;
                    $sku->save();
                }
            } else {
                $product = Product::find($goods->product_id);
                if ($product) {
                    $product->stock = $product->stock + $goods->num;
                    $product->save();
                }
            }
        }
    }
}
