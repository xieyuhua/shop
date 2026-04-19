<?php

declare(strict_types=1);

namespace app\common\entity;

use app\common\model\Order as OrderModel;
use app\common\model\OrderGoods as OrderGoodsModel;
use app\common\model\Cart as CartModel;
use app\common\model\Product as ProductModel;
use app\common\model\ProductSku as ProductSkuModel;
use app\common\model\UserAddress as UserAddressModel;
use app\common\model\Payment as PaymentModel;
use app\common\model\PointsLog as PointsLogModel;
use app\common\model\BalanceLog as BalanceLogModel;
use app\common\model\OrderEvaluate as OrderEvaluateModel;

/**
 * 订单实体 - 处理订单相关业务逻辑
 */
class OrderEntity
{
    /**
     * 创建订单
     */
    public function create(int $userId, array $data): array
    {
        $addressId = $data['address_id'] ?? 0;
        $cartIds = $data['cart_ids'] ?? [];
        $remark = $data['remark'] ?? '';

        // 验证收货地址
        $address = UserAddressModel::where('id', $addressId)
            ->where('user_id', $userId)
            ->find();
        if (!$address) {
            return ['success' => false, 'msg' => '收货地址不存在'];
        }

        // 获取购物车商品
        if (empty($cartIds)) {
            return ['success' => false, 'msg' => '请选择商品'];
        }

        $carts = CartModel::with(['product', 'sku'])
            ->whereIn('id', $cartIds)
            ->where('user_id', $userId)
            ->where('selected', 1)
            ->select();

        if ($carts->isEmpty()) {
            return ['success' => false, 'msg' => '请选择有效的商品'];
        }

        // 构建订单商品数据并验证
        $orderGoodsData = [];
        $totalNum = 0;
        $totalPrice = 0;
        $shopIds = [];

        foreach ($carts as $cart) {
            if (!$cart->product || !$cart->product->is_on_sale) {
                return ['success' => false, 'msg' => '商品已下架：' . ($cart->product->name ?? '')];
            }

            $product = $cart->product;
            $sku = $cart->sku;

            $price = $product->price;
            $stock = $product->stock;
            $skuName = '';

            if ($sku) {
                $price = $sku->price;
                $stock = $sku->stock;
                $skuName = $sku->sku_name;
            }

            if ($stock < $cart->num) {
                return ['success' => false, 'msg' => '库存不足：' . $product->name];
            }

            $shopIds[] = $cart->shop_id;

            $orderGoodsData[] = [
                'product_id' => $product->id,
                'sku_id' => $cart->sku_id,
                'shop_id' => $cart->shop_id,
                'user_id' => $userId,
                'goods_name' => $product->name,
                'goods_image' => $product->image,
                'sku_name' => $skuName,
                'price' => $price,
                'num' => $cart->num,
                'total_price' => $price * $cart->num,
            ];

            $totalNum += $cart->num;
            $totalPrice += $price * $cart->num;
        }

        $shopId = $shopIds[0];
        $freightPrice = $this->calculateFreight($carts);

        // 创建订单
        $order = new OrderModel();
        $order->user_id = $userId;
        $order->shop_id = $shopId;
        $order->order_no = $this->createOrderNo();
        $order->total_num = $totalNum;
        $order->total_price = $totalPrice;
        $order->freight_price = $freightPrice;
        $order->discount_price = $data['discount_price'] ?? 0;
        $order->pay_price = $totalPrice + $freightPrice - ($data['discount_price'] ?? 0);
        $order->points_discount = $data['points_discount'] ?? 0;
        $order->coupon_id = $data['coupon_id'] ?? 0;
        $order->coupon_price = $data['coupon_price'] ?? 0;
        $order->address_id = $addressId;
        $order->remark = $remark;
        $order->order_status = OrderModel::STATUS_PENDING_PAY;
        $order->pay_status = OrderModel::PAY_STATUS_UNPAID;
        $order->save();

        // 创建订单商品
        foreach ($orderGoodsData as $goods) {
            $orderGoods = new OrderGoodsModel();
            $orderGoods->order_id = $order->id;
            $orderGoods->product_id = $goods['product_id'];
            $orderGoods->sku_id = $goods['sku_id'];
            $orderGoods->shop_id = $goods['shop_id'];
            $orderGoods->user_id = $goods['user_id'];
            $orderGoods->goods_name = $goods['goods_name'];
            $orderGoods->goods_image = $goods['goods_image'];
            $orderGoods->sku_name = $goods['sku_name'];
            $orderGoods->price = $goods['price'];
            $orderGoods->num = $goods['num'];
            $orderGoods->total_price = $goods['total_price'];
            $orderGoods->save();

            // 扣减库存
            if ($goods['sku_id'] > 0) {
                $sku = ProductSkuModel::find($goods['sku_id']);
                if ($sku) {
                    $sku->stock = $sku->stock - $goods['num'];
                    $sku->save();
                }
            } else {
                $product = ProductModel::find($goods['product_id']);
                if ($product) {
                    $product->stock = $product->stock - $goods['num'];
                    $product->save();
                }
            }
        }

        // 删除已下单的购物车商品
        CartModel::whereIn('id', $cartIds)
            ->where('user_id', $userId)
            ->delete();

        return [
            'success' => true,
            'data' => [
                'order_id' => $order->id,
                'order_no' => $order->order_no,
                'pay_price' => $order->pay_price,
            ],
        ];
    }

    /**
     * 订单列表
     */
    public function getList(int $userId, int $status = -1, int $page = 1, int $limit = 15): array
    {
        $query = OrderModel::with(['shop', 'orderGoods.product', 'address'])
            ->where('user_id', $userId)
            ->where('is_delete', 0)
            ->order('create_time', 'desc');

        if ($status >= 0) {
            $query->where('order_status', $status);
        }

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
     * 订单详情
     */
    public function getDetail(int $orderId, int $userId): array
    {
        $order = OrderModel::with(['shop', 'address', 'orderGoods.product', 'orderGoods.sku'])
            ->where('id', $orderId)
            ->where('user_id', $userId)
            ->find();

        if (!$order) {
            return ['success' => false, 'msg' => '订单不存在'];
        }

        return [
            'success' => true,
            'data' => $order,
        ];
    }

    /**
     * 取消订单
     */
    public function cancel(int $orderId, int $userId, string $reason = ''): array
    {
        $order = OrderModel::where('id', $orderId)
            ->where('user_id', $userId)
            ->find();

        if (!$order) {
            return ['success' => false, 'msg' => '订单不存在'];
        }

        if ($order->order_status != OrderModel::STATUS_PENDING_PAY) {
            return ['success' => false, 'msg' => '订单状态不允许取消'];
        }

        $order->order_status = OrderModel::STATUS_CANCELLED;
        $order->cancel_time = time();
        $order->cancel_reason = $reason ?: '用户取消';
        $order->save();

        // 恢复库存
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

        return ['success' => true];
    }

    /**
     * 支付订单
     */
    public function pay(int $orderId, int $userId, int $payType, int $balance = 0): array
    {
        $order = OrderModel::where('id', $orderId)
            ->where('user_id', $userId)
            ->find();

        if (!$order) {
            return ['success' => false, 'msg' => '订单不存在'];
        }

        if ($order->pay_status == OrderModel::PAY_STATUS_PAID) {
            return ['success' => false, 'msg' => '订单已支付'];
        }

        if ($payType == PaymentModel::TYPE_BALANCE) {
            $user = \app\common\model\User::find($userId);
            if ($user->balance < $order->pay_price) {
                return ['success' => false, 'msg' => '余额不足'];
            }

            // 扣除余额
            $user->balance = $user->balance - $order->pay_price;
            $user->save();

            // 记录余额日志
            $balanceLog = new BalanceLogModel();
            $balanceLog->user_id = $userId;
            $balanceLog->change_type = BalanceLogModel::TYPE_EXPEND;
            $balanceLog->balance = -$order->pay_price;
            $balanceLog->description = '支付订单：' . $order->order_no;
            $balanceLog->source_type = 'order';
            $balanceLog->source_id = $order->id;
            $balanceLog->create_time = time();
            $balanceLog->save();

            // 更新订单状态
            $order->pay_type = $payType;
            $order->pay_status = OrderModel::PAY_STATUS_PAID;
            $order->pay_time = time();
            $order->order_status = OrderModel::STATUS_PENDING_DELIVERY;
            $order->save();

            // 赠送积分
            $pointsLog = new PointsLogModel();
            $pointsLog->user_id = $userId;
            $pointsLog->change_type = PointsLogModel::TYPE_INCOME;
            $pointsLog->points = floor($order->pay_price);
            $pointsLog->description = '订单消费赠送积分';
            $pointsLog->source_type = 'order';
            $pointsLog->source_id = $order->id;
            $pointsLog->create_time = time();
            $pointsLog->save();

            return [
                'success' => true,
                'data' => [
                    'pay_status' => 'success',
                    'pay_type' => 'balance',
                ],
            ];
        }

        // 创建第三方支付记录
        $payment = new PaymentModel();
        $payment->user_id = $userId;
        $payment->order_id = $orderId;
        $payment->pay_type = $payType;
        $payment->amount = $order->pay_price;
        $payment->status = PaymentModel::STATUS_PENDING;
        $payment->create_time = time();
        $payment->save();

        return [
            'success' => true,
            'data' => [
                'payment_id' => $payment->id,
                'pay_price' => $payment->amount,
                'pay_type' => $payType,
            ],
        ];
    }

    /**
     * 确认收货
     */
    public function receive(int $orderId, int $userId): array
    {
        $order = OrderModel::where('id', $orderId)
            ->where('user_id', $userId)
            ->find();

        if (!$order) {
            return ['success' => false, 'msg' => '订单不存在'];
        }

        if ($order->order_status != OrderModel::STATUS_PENDING_RECEIVE) {
            return ['success' => false, 'msg' => '订单状态不允许收货'];
        }

        $order->receive_time = time();
        $order->order_status = OrderModel::STATUS_PENDING_COMMENT;
        $order->save();

        return ['success' => true];
    }

    /**
     * 评价订单
     */
    public function comment(int $orderId, int $userId, array $data): array
    {
        $order = OrderModel::where('id', $orderId)
            ->where('user_id', $userId)
            ->find();

        if (!$order) {
            return ['success' => false, 'msg' => '订单不存在'];
        }

        if ($order->order_status != OrderModel::STATUS_PENDING_COMMENT) {
            return ['success' => false, 'msg' => '订单状态不允许评价'];
        }

        $goodsList = OrderGoodsModel::where('order_id', $orderId)
            ->where('is_comment', 0)
            ->select();

        foreach ($goodsList as $goods) {
            $evaluate = new OrderEvaluateModel();
            $evaluate->order_id = $orderId;
            $evaluate->order_goods_id = $goods->id;
            $evaluate->product_id = $goods->product_id;
            $evaluate->shop_id = $goods->shop_id;
            $evaluate->user_id = $userId;
            $evaluate->score = $data['score'] ?? 5;
            $evaluate->score_describe = $data['score_describe'] ?? 5;
            $evaluate->score_service = $data['score_service'] ?? 5;
            $evaluate->score_logistics = $data['score_logistics'] ?? 5;
            $evaluate->content = $data['content'] ?? '';
            $evaluate->images = is_array($data['images'] ?? []) ? json_encode($data['images']) : '';
            $evaluate->create_time = time();
            $evaluate->save();

            $goods->is_comment = 1;
            $goods->save();
        }

        $order->is_comment = 1;
        $order->save();

        return ['success' => true];
    }

    /**
     * 获取订单数量统计
     */
    public function getCount(int $userId): array
    {
        return [
            'success' => true,
            'data' => [
                'pending_pay' => OrderModel::where('user_id', $userId)->where('order_status', OrderModel::STATUS_PENDING_PAY)->count(),
                'pending_delivery' => OrderModel::where('user_id', $userId)->where('order_status', OrderModel::STATUS_PENDING_DELIVERY)->count(),
                'pending_receive' => OrderModel::where('user_id', $userId)->where('order_status', OrderModel::STATUS_PENDING_RECEIVE)->count(),
                'pending_comment' => OrderModel::where('user_id', $userId)->where('order_status', OrderModel::STATUS_PENDING_COMMENT)->count(),
            ],
        ];
    }

    /**
     * 计算运费
     */
    private function calculateFreight($carts): float
    {
        $totalWeight = 0;
        $totalPrice = 0;

        foreach ($carts as $cart) {
            $product = $cart->product;
            $weight = $product->weight ?? 0;
            $price = $cart->sku_id > 0 ? ($cart->sku ? $cart->sku->price : 0) : ($product ? $product->price : 0);
            $totalWeight += $weight * $cart->num;
            $totalPrice += $price * $cart->num;
        }

        if ($totalPrice >= 99) {
            return 0;
        }

        return 10;
    }

    /**
     * 生成订单号
     */
    private function createOrderNo(): string
    {
        return date('YmdHis') . rand(100000, 999999);
    }
}
