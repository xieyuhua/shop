<?php

declare(strict_types=1);

namespace app\api\controller;

use app\common\controller\BaseController;
use app\common\entity\AftersaleEntity;

/**
 * 售后控制器 - 仅接收参数和返回结果
 */
class Aftersale extends BaseController
{
    private AftersaleEntity $entity;

    public function __construct()
    {
        parent::__construct();
        $this->entity = new AftersaleEntity();
    }

    /**
     * 申请售后
     */
    public function apply(): \think\Response
    {
        $this->auth();

        $data = $this->request->post();

        $result = $this->entity->apply($this->userId, $data);

        if (!$result['success']) {
            return $this->error($result['msg']);
        }

        return $this->success($result['data']);
    }

    /**
     * 售后列表
     */
    public function list(): \think\Response
    {
        $this->auth();

        $page = (int) ($this->request->get('page', 1));
        $limit = (int) ($this->request->get('limit', 15));

        $result = $this->entity->getList($this->userId, $page, $limit);

        return $this->success($result['data']);
    }

    /**
     * 售后详情
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
     * 取消售后
     */
    public function cancel(): \think\Response
    {
        $this->auth();

        $id = (int) ($this->request->post('id', 0));

        $result = $this->entity->cancel($id, $this->userId);

        if (!$result['success']) {
            return $this->error($result['msg']);
        }

        return $this->success();
    }

    /**
     * 邮寄商品（退货时）
     */
    public function deliver(): \think\Response
    {
        $this->auth();

        $id = (int) ($this->request->post('id', 0));
        $data = $this->request->post();

        $result = $this->entity->deliver($id, $this->userId, $data);

        if (!$result['success']) {
            return $this->error($result['msg']);
        }

        return $this->success();
    }
}
