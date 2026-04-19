<?php

declare(strict_types=1);

namespace app\api\controller;

use app\common\controller\BaseController;
use app\common\entity\OrderEntity;

/**
 * 订单控制器 - 仅接收参数和返回结果
 */
class Order extends BaseController
{
    private OrderEntity $entity;

    public function __construct()
    {
        parent::__construct();
        $this->entity = new OrderEntity();
    }

    /**
     * 创建订单
     */
    public function create(): \think\Response
    {
        $this->auth();

        $data = $this->request->post();

        $result = $this->entity->create($this->userId, $data);

        if (!$result['success']) {
            return $this->error($result['msg']);
        }

        return $this->success($result['data']);
    }

    /**
     * 订单列表
     */
    public function list(): \think\Response
    {
        $this->auth();

        $status = (int) ($this->request->get('status', -1));
        $page = (int) ($this->request->get('page', 1));
        $limit = (int) ($this->request->get('limit', 15));

        $result = $this->entity->getList($this->userId, $status, $page, $limit);

        return $this->success($result['data']);
    }

    /**
     * 订单详情
     */
    public function detail(): \think\Response
    {
        $this->auth();

        $id = (int) ($this->request->get('id', 0));

        $result = $this->entity->getDetail($id, $this->userId);

        if (!$result['success']) {
            return $this->error($result['msg']);
        }

        return $this->success($result['data']);
    }

    /**
     * 取消订单
     */
    public function cancel(): \think\Response
    {
        $this->auth();

        $id = (int) ($this->request->post('id', 0));
        $reason = $this->request->post('reason', '');

        $result = $this->entity->cancel($id, $this->userId, $reason);

        if (!$result['success']) {
            return $this->error($result['msg']);
        }

        return $this->success();
    }

    /**
     * 支付订单
     */
    public function pay(): \think\Response
    {
        $this->auth();

        $id = (int) ($this->request->post('id', 0));
        $payType = (int) ($this->request->post('pay_type', 1));

        $result = $this->entity->pay($id, $this->userId, $payType);

        if (!$result['success']) {
            return $this->error($result['msg']);
        }

        return $this->success($result['data']);
    }

    /**
     * 确认收货
     */
    public function receive(): \think\Response
    {
        $this->auth();

        $id = (int) ($this->request->post('id', 0));

        $result = $this->entity->receive($id, $this->userId);

        if (!$result['success']) {
            return $this->error($result['msg']);
        }

        return $this->success();
    }

    /**
     * 评价订单
     */
    public function comment(): \think\Response
    {
        $this->auth();

        $id = (int) ($this->request->post('order_id', 0));
        $data = $this->request->post();

        $result = $this->entity->comment($id, $this->userId, $data);

        if (!$result['success']) {
            return $this->error($result['msg']);
        }

        return $this->success();
    }

    /**
     * 获取订单数量统计
     */
    public function getCount(): \think\Response
    {
        $this->auth();

        $result = $this->entity->getCount($this->userId);

        return $this->success($result['data']);
    }
}
