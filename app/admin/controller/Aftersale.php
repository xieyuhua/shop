<?php

declare(strict_types=1);

namespace app\admin\controller;

use app\common\controller\BaseController;
use app\common\entity\AdminAftersaleEntity;

/**
 * 后台售后管理控制器 - 仅接收参数和返回结果
 */
class Aftersale extends BaseController
{
    private AdminAftersaleEntity $aftersaleEntity;

    public function __construct()
    {
        parent::__construct();
        $this->aftersaleEntity = new AdminAftersaleEntity();
    }

    /**
     * 售后列表
     */
    public function list(): \think\Response
    {
        $this->adminAuth();

        $params = [
            'page' => $this->request->get('page', 1),
            'limit' => $this->request->get('limit', 15),
            'status' => $this->request->get('status', ''),
            'type' => $this->request->get('type', ''),
        ];

        $result = $this->aftersaleEntity->getList($params);

        return $this->success($result);
    }

    /**
     * 售后详情
     */
    public function detail(): \think\Response
    {
        $this->adminAuth();

        $id = (int) $this->request->get('id', 0);

        $aftersale = $this->aftersaleEntity->getDetail($id);

        if (!$aftersale) {
            return $this->error('售后申请不存在');
        }

        return $this->success($aftersale);
    }

    /**
     * 同意售后（退款）
     */
    public function agree(): \think\Response
    {
        $this->adminAuth();

        $id = (int) $this->request->post('id', 0);

        $result = $this->aftersaleEntity->agree($id);

        if (!$result['success']) {
            return $this->error($result['msg']);
        }

        return $this->success();
    }

    /**
     * 拒绝售后
     */
    public function refuse(): \think\Response
    {
        $this->adminAuth();

        $id = (int) $this->request->post('id', 0);
        $reason = $this->request->post('reason', '');

        $result = $this->aftersaleEntity->refuse($id, $reason);

        if (!$result['success']) {
            return $this->error($result['msg']);
        }

        return $this->success();
    }

    /**
     * 确认收货退货
     */
    public function confirmReturn(): \think\Response
    {
        $this->adminAuth();

        $id = (int) $this->request->post('id', 0);

        $result = $this->aftersaleEntity->confirmReturn($id);

        if (!$result['success']) {
            return $this->error($result['msg']);
        }

        return $this->success();
    }
}
