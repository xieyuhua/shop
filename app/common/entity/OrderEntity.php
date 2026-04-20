<?php

declare(strict_types=1);

namespace app\common\entity;

use app\common\model\Order;
use app\common\model\OrderGoods;
use app\common\model\Cart;
use app\common\model\Product;
use app\common\model\ProductSku;
use app\common\model\Payment;
use app\common\model\PointsLog;
use app\common\model\BalanceLog;
use app\common\model\OrderEvaluate;
use app\common\model\User;
use app\common\model\UserAddress;
use app\common\model\Shop;
use think\model\concern\SoftDelete;

/**
 * 订单实体
 */
class OrderEntity extends BaseEntity
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
        'coupon_price' => 'float',
        'points_discount' => 'float',
    ];

    // ========== 业务逻辑 ==========

    /**
     * 创建订单
     */
    public function create(int $userId, array $data): array
    {
        $addressId = $data['address_id'] ?? 0;
        $cartIds = $data['cart_ids'] ?? [];
        $remark = $data['remark'] ?? '';

        $address = UserAddress::where('id', $addressId)
            ->where('user_id', $userId)
            ->find();
        if (!$address) {
            return ['success' => false, 'msg' => '收货地址不存在'];
        }

        if (empty($cartIds)) {
            return ['success' => false, 'msg' => '请选择商品'];
        }

        $carts = Cart::with(['product', 'sku'])
            ->whereIn('id', $cartIds)
            ->where('user_id', $userId)
            ->where('selected', 1)
            ->select();

        if ($carts->isEmpty()) {
            return ['success' => false, 'msg' => '请选择有效的商品'];
        }

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

        $order = new self();
        $order->user_id = $userId;
        $order->shop_id = $shopIds[0];
        $order->order_no = $this->createOrderNo();
        $order->total_num = $totalNum;
        $order->total_price = $totalPrice;
        $order->freight_price = $this->calculateFreight($carts);
        $order->discount_price = $data['discount_price'] ?? 0;
        $order->pay_price = $totalPrice + $order->freight_price - ($data['discount_price'] ?? 0);
        $order->points_discount = $data['points_discount'] ?? 0;
        $order->coupon_id = $data['coupon_id'] ?? 0;
        $order->coupon_price = $data['coupon_price'] ?? 0;
        $order->address_id = $addressId;
        $order->remark = $remark;
        $order->order_status = Order::STATUS_PENDING_PAY;
        $order->pay_status = Order::PAY_STATUS_UNPAID;
        $order->save();

        foreach ($orderGoodsData as $goods) {
            $orderGoods = new OrderGoods();
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

            if ($goods['sku_id'] > 0) {
                $sku = ProductSku::find($goods['sku_id']);
                if ($sku) {
                    $sku->stock = $sku->stock - $goods['num'];
                    $sku->save();
                }
            } else {
                $product = Product::find($goods['product_id']);
                if ($product) {
                    $product->stock = $product->stock - $goods['num'];
                    $product->save();
                }
            }
        }

        Cart::whereIn('id', $cartIds)
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
        $query = self::with(['shop', 'orderGoods.product', 'address'])
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
        $order = self::with(['shop', 'address', 'orderGoods.product', 'orderGoods.sku'])
            ->where('id', $orderId)
            ->where('user_id', $userId)
            ->find();

        if (!$order) {
            return ['success' => false, 'msg' => '订单不存在'];
        }

        return ['success' => true, 'data' => $order];
    }

    /**
     * 取消订单
     */
    public function cancel(int $orderId, int $userId, string $reason = ''): array
    {
        $order = self::where('id', $orderId)
            ->where('user_id', $userId)
            ->find();

        if (!$order) {
            return ['success' => false, 'msg' => '订单不存在'];
        }

        if ($order->order_status != Order::STATUS_PENDING_PAY) {
            return ['success' => false, 'msg' => '订单状态不允许取消'];
        }

        $order->order_status = Order::STATUS_CANCELLED;
        $order->cancel_time = time();
        $order->cancel_reason = $reason ?: '用户取消';
        $order->save();

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

        return ['success' => true];
    }

    /**
     * 支付订单
     */
    public function pay(int $orderId, int $userId, int $payType, int $balance = 0): array
    {
        $order = self::where('id', $orderId)
            ->where('user_id', $userId)
            ->find();

        if (!$order) {
            return ['success' => false, 'msg' => '订单不存在'];
        }

        if ($order->pay_status == Order::PAY_STATUS_PAID) {
            return ['success' => false, 'msg' => '订单已支付'];
        }

        if ($payType == Payment::TYPE_BALANCE) {
            $user = User::find($userId);
            if ($user->balance < $order->pay_price) {
                return ['success' => false, 'msg' => '余额不足'];
            }

            $user->balance = $user->balance - $order->pay_price;
            $user->save();

            $balanceLog = new BalanceLog();
            $balanceLog->user_id = $userId;
            $balanceLog->change_type = BalanceLog::TYPE_EXPEND;
            $balanceLog->balance = -$order->pay_price;
            $balanceLog->description = '支付订单：' . $order->order_no;
            $balanceLog->source_type = 'order';
            $balanceLog->source_id = $order->id;
            $balanceLog->save();

            $order->pay_type = $payType;
            $order->pay_status = Order::PAY_STATUS_PAID;
            $order->pay_time = time();
            $order->order_status = Order::STATUS_PENDING_DELIVERY;
            $order->save();

            $pointsLog = new PointsLog();
            $pointsLog->user_id = $userId;
            $pointsLog->change_type = PointsLog::TYPE_INCOME;
            $pointsLog->points = floor($order->pay_price);
            $pointsLog->description = '订单消费赠送积分';
            $pointsLog->source_type = 'order';
            $pointsLog->source_id = $order->id;
            $pointsLog->save();

            return ['success' => true, 'data' => ['pay_status' => 'success', 'pay_type' => 'balance']];
        }

        $payment = new Payment();
        $payment->user_id = $userId;
        $payment->order_id = $orderId;
        $payment->pay_type = $payType;
        $payment->amount = $order->pay_price;
        $payment->status = Payment::STATUS_PENDING;
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
        $order = self::where('id', $orderId)
            ->where('user_id', $userId)
            ->find();

        if (!$order) {
            return ['success' => false, 'msg' => '订单不存在'];
        }

        if ($order->order_status != Order::STATUS_PENDING_RECEIVE) {
            return ['success' => false, 'msg' => '订单状态不允许收货'];
        }

        $order->receive_time = time();
        $order->order_status = Order::STATUS_PENDING_COMMENT;
        $order->save();

        return ['success' => true];
    }

    /**
     * 评价订单
     */
    public function comment(int $orderId, int $userId, array $data): array
    {
        $order = self::where('id', $orderId)
            ->where('user_id', $userId)
            ->find();

        if (!$order) {
            return ['success' => false, 'msg' => '订单不存在'];
        }

        if ($order->order_status != Order::STATUS_PENDING_COMMENT) {
            return ['success' => false, 'msg' => '订单状态不允许评价'];
        }

        $goodsList = OrderGoods::where('order_id', $orderId)
            ->where('is_comment', 0)
            ->select();

        foreach ($goodsList as $goods) {
            $evaluate = new OrderEvaluate();
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
                'pending_pay' => self::where('user_id', $userId)->where('order_status', Order::STATUS_PENDING_PAY)->count(),
                'pending_delivery' => self::where('user_id', $userId)->where('order_status', Order::STATUS_PENDING_DELIVERY)->count(),
                'pending_receive' => self::where('user_id', $userId)->where('order_status', Order::STATUS_PENDING_RECEIVE)->count(),
                'pending_comment' => self::where('user_id', $userId)->where('order_status', Order::STATUS_PENDING_COMMENT)->count(),
            ],
        ];
    }

    // ========== 私有方法 ==========

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

        return $totalPrice >= 99 ? 0 : 10;
    }

    private function createOrderNo(): string
    {
        return date('YmdHis') . rand(100000, 999999);
    }
}
