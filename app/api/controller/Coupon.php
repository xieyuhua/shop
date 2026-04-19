<?php

declare(strict_types=1);

namespace app\api\controller;

use app\common\controller\BaseController;
use app\common\entity\CouponEntity;

/**
 * 优惠券控制器 - 仅接收参数和返回结果
 */
class CouponController extends BaseController
{
    private CouponEntity $couponEntity;

    public function __construct()
    {
        parent::__construct();
        $this->couponEntity = new CouponEntity();
    }

    /**
     * 优惠券列表
     */
    public function list(): \think\Response
    {
        $params = [
            'page' => $this->request->get('page', 1),
            'limit' => $this->request->get('limit', 15),
            'shop_id' => $this->request->get('shop_id', 0),
        ];

        $result = $this->couponEntity->getList($params);

        return $this->success($result);
    }

    /**
     * 我的优惠券
     */
    public function myList(): \think\Response
    {
        $this->auth();

        $params = [
            'page' => $this->request->get('page', 1),
            'limit' => $this->request->get('limit', 15),
            'status' => $this->request->get('status', ''),
        ];

        $result = $this->couponEntity->getMyList($this->userId, $params);

        return $this->success($result);
    }

    /**
     * 领取优惠券
     */
    public function receive(): \think\Response
    {
        $this->auth();

        $couponId = (int) $this->request->post('coupon_id');

        $result = $this->couponEntity->receive($this->userId, $couponId);

        if (!$result['success']) {
            return $this->error($result['msg']);
        }

        return $this->success($result['data'] ?? []);
    }

    /**
     * 可用优惠券
     */
    public function available(): \think\Response
    {
        $this->auth();

        $params = [
            'money' => (float) $this->request->get('money', 0),
            'shop_id' => (int) $this->request->get('shop_id', 0),
            'category_id' => (int) $this->request->get('category_id', 0),
            'product_id' => (int) $this->request->get('product_id', 0),
        ];

        $coupons = $this->couponEntity->getAvailable(
            $this->userId,
            $params['money'],
            $params['shop_id'],
            $params['category_id'],
            $params['product_id']
        );

        return $this->success($coupons);
    }

    /**
     * 优惠券详情
     */
    public function detail(): \think\Response
    {
        $id = (int) $this->request->get('id');

        $coupon = $this->couponEntity->getDetail($id);

        if (!$coupon) {
            return $this->error('优惠券不存在');
        }

        return $this->success($coupon);
    }
}
