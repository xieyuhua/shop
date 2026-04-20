<?php

declare(strict_types=1);

namespace app\common\entity;

use app\common\model\Cart;
use app\common\model\Product;
use app\common\model\ProductSku;
use app\common\model\Shop;

/**
 * 购物车实体
 */
class CartEntity extends BaseEntity
{
    protected $table = 'cart';

    protected $type = [
        'num' => 'integer',
    ];

    // ========== 业务逻辑 ==========

    /**
     * 添加商品到购物车
     */
    public function add(int $userId, int $productId, int $num, int $skuId = 0): array
    {
        $product = Product::find($productId);
        if (!$product || !$product->is_on_sale) {
            return ['success' => false, 'msg' => '商品已下架'];
        }

        if ($skuId > 0) {
            $sku = ProductSku::find($skuId);
            if (!$sku || $sku->product_id != $productId) {
                return ['success' => false, 'msg' => 'SKU不存在'];
            }
            if ($sku->stock < $num) {
                return ['success' => false, 'msg' => '库存不足'];
            }
        } else {
            if ($product->stock < $num) {
                return ['success' => false, 'msg' => '库存不足'];
            }
        }

        $cart = self::where([
            'user_id' => $userId,
            'product_id' => $productId,
            'sku_id' => $skuId,
        ])->find();

        if ($cart) {
            $cart->num = $cart->num + $num;
            $cart->save();
        } else {
            $cart = new self();
            $cart->user_id = $userId;
            $cart->product_id = $productId;
            $cart->sku_id = $skuId;
            $cart->shop_id = $product->shop_id;
            $cart->num = $num;
            $cart->selected = 1;
            $cart->save();
        }

        return ['success' => true, 'data' => $cart];
    }

    /**
     * 更新购物车商品数量
     */
    public function updateNum(int $cartId, int $userId, int $num): array
    {
        $cart = self::where('id', $cartId)
            ->where('user_id', $userId)
            ->find();

        if (!$cart) {
            return ['success' => false, 'msg' => '购物车商品不存在'];
        }

        if ($num <= 0) {
            $cart->delete();
            return ['success' => true, 'data' => ['deleted' => true]];
        }

        if ($cart->sku_id > 0) {
            $sku = ProductSku::find($cart->sku_id);
            if (!$sku || $sku->stock < $num) {
                return ['success' => false, 'msg' => '库存不足'];
            }
        } else {
            $product = Product::find($cart->product_id);
            if (!$product || $product->stock < $num) {
                return ['success' => false, 'msg' => '库存不足'];
            }
        }

        $cart->num = $num;
        $cart->save();

        return ['success' => true, 'data' => $cart];
    }

    /**
     * 删除购物车商品
     */
    public function delete(int $cartId, int $userId): array
    {
        $cart = self::where('id', $cartId)
            ->where('user_id', $userId)
            ->find();

        if (!$cart) {
            return ['success' => false, 'msg' => '购物车商品不存在'];
        }

        $cart->delete();

        return ['success' => true];
    }

    /**
     * 清空购物车
     */
    public function clear(int $userId): array
    {
        self::where('user_id', $userId)->delete();

        return ['success' => true];
    }

    /**
     * 获取购物车列表
     */
    public function getList(int $userId): array
    {
        $carts = self::with(['product', 'sku', 'shop'])
            ->where('user_id', $userId)
            ->select();

        $list = [];
        foreach ($carts as $cart) {
            $shopId = $cart->shop_id;
            if (!isset($list[$shopId])) {
                $list[$shopId] = [
                    'shop_id' => $shopId,
                    'shop_name' => $cart->shop ? $cart->shop->shop_name : '',
                    'items' => [],
                    'total_num' => 0,
                    'total_price' => 0,
                ];
            }

            $price = $cart->sku_id > 0 ? ($cart->sku ? $cart->sku->price : 0) : ($cart->product ? $cart->product->price : 0);
            $stock = $cart->sku_id > 0 ? ($cart->sku ? $cart->sku->stock : 0) : ($cart->product ? $cart->product->stock : 0);

            $item = [
                'id' => $cart->id,
                'product_id' => $cart->product_id,
                'sku_id' => $cart->sku_id,
                'product_name' => $cart->product ? $cart->product->name : '',
                'product_image' => $cart->product ? $cart->product->image : '',
                'sku_name' => $cart->sku ? $cart->sku->sku_name : '',
                'price' => $price,
                'num' => $cart->num,
                'total_price' => $price * $cart->num,
                'selected' => $cart->selected,
                'stock' => $stock,
                'is_on_sale' => $cart->product ? $cart->product->is_on_sale : 0,
            ];

            $list[$shopId]['items'][] = $item;
            $list[$shopId]['total_num'] += $cart->num;
            $list[$shopId]['total_price'] += $item['total_price'];
        }

        return ['success' => true, 'data' => array_values($list)];
    }

    /**
     * 获取选中商品统计
     */
    public function getSelectedTotal(int $userId): array
    {
        $carts = self::with(['product', 'sku'])
            ->where('user_id', $userId)
            ->where('selected', 1)
            ->select();

        $total = 0;
        $num = 0;
        foreach ($carts as $cart) {
            $price = $cart->sku_id > 0 ? ($cart->sku ? $cart->sku->price : 0) : ($cart->product ? $cart->product->price : 0);
            $total += $price * $cart->num;
            $num += $cart->num;
        }

        return ['success' => true, 'data' => ['total' => $total, 'num' => $num]];
    }

    /**
     * 切换选中状态
     */
    public function toggleSelected(int $cartId, int $userId): array
    {
        $cart = self::where('id', $cartId)
            ->where('user_id', $userId)
            ->find();

        if (!$cart) {
            return ['success' => false, 'msg' => '购物车商品不存在'];
        }

        $cart->selected = $cart->selected ? 0 : 1;
        $cart->save();

        return ['success' => true, 'data' => $cart];
    }

    /**
     * 全选/取消全选
     */
    public function selectAll(int $userId, int $selected = 1): array
    {
        self::where('user_id', $userId)->update(['selected' => $selected]);

        return ['success' => true];
    }

    /**
     * 删除已选商品
     */
    public function deleteSelected(int $userId): array
    {
        self::where('user_id', $userId)
            ->where('selected', 1)
            ->delete();

        return ['success' => true];
    }
}
