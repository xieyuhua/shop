<?php

declare(strict_types=1);

namespace app\admin\controller;

use app\common\controller\BaseController;
use app\common\entity\AdminOrderEntity;

/**
 * 后台订单管理控制器 - 仅接收参数和返回结果
 */
class Order extends BaseController
{
    private AdminOrderEntity $orderEntity;

    public function __construct()
    {
        parent::__construct();
        $this->orderEntity = new AdminOrderEntity();
    }

    /**
     * 订单列表
     */
    public function list(): \think\Response
    {
        $this->adminAuth();

        $params = [
            'page' => $this->request->get('page', 1),
            'limit' => $this->request->get('limit', 15),
            'status' => $this->request->get('status', ''),
            'keyword' => $this->request->get('keyword', ''),
            'date_range' => $this->request->get('date_range', ''),
        ];

        $result = $this->orderEntity->getList($params);

        return $this->success($result);
    }

    /**
     * 订单详情
     */
    public function detail(): \think\Response
    {
        $this->adminAuth();

        $id = (int) $this->request->get('id', 0);

        $order = $this->orderEntity->getDetail($id);

        if (!$order) {
            return $this->error('订单不存在');
        }

        return $this->success($order);
    }

    /**
     * 发货
     */
    public function delivery(): \think\Response
    {
        $this->adminAuth();

        $id = (int) $this->request->post('id', 0);
        $expressCompany = $this->request->post('express_company', '');
        $expressNo = $this->request->post('express_no', '');

        $result = $this->orderEntity->delivery($id, $expressCompany, $expressNo);

        if (!$result['success']) {
            return $this->error($result['msg']);
        }

        return $this->success();
    }

    /**
     * 修改价格
     */
    public function updatePrice(): \think\Response
    {
        $this->adminAuth();

        $id = (int) $this->request->post('id', 0);
        $freightPrice = (float) $this->request->post('freight_price', 0);

        $result = $this->orderEntity->updatePrice($id, $freightPrice);

        if (!$result['success']) {
            return $this->error($result['msg']);
        }

        return $this->success();
    }

    /**
     * 关闭订单
     */
    public function close(): \think\Response
    {
        $this->adminAuth();

        $id = (int) $this->request->post('id', 0);
        $reason = $this->request->post('reason', '管理员关闭');

        $result = $this->orderEntity->close($id, $reason, $this->adminId);

        if (!$result['success']) {
            return $this->error($result['msg']);
        }

        return $this->success();
    }

    /**
     * 导出订单
     */
    public function export(): \think\Response
    {
        $this->adminAuth();

        // 简化实现，实际可使用 fast-excel 等库
        return $this->success([], '导出功能开发中');
    }
}
