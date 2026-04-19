<?php

declare(strict_types=1);

namespace app\api\controller;

use app\common\controller\BaseController;
use app\common\entity\UserAddressEntity;

/**
 * 收货地址控制器 - 仅接收参数和返回结果
 */
class Address extends BaseController
{
    private UserAddressEntity $entity;

    public function __construct()
    {
        parent::__construct();
        $this->entity = new UserAddressEntity();
    }

    /**
     * 地址列表
     */
    public function list(): \think\Response
    {
        $this->auth();

        $result = $this->entity->getList($this->userId);

        return $this->success($result['data']);
    }

    /**
     * 地址详情
     */
    public function detail(): \think\Response
    {
        $this->auth();

        $id = (int) $this->request->get('id');

        $result = $this->entity->getDetail($id, $this->userId);

        if (!$result['success']) {
            return $this->error($result['msg']);
        }

        return $this->success($result['data']);
    }

    /**
     * 创建地址
     */
    public function create(): \think\Response
    {
        $this->auth();

        $data = $this->request->post();

        $result = $this->entity->create($this->userId, $data);

        if (!$result['success']) {
            if (!empty($result['errors'])) {
                $errors = array_values($result['errors']);
                return $this->error($errors[0]);
            }
            return $this->error($result['msg'] ?? '创建失败');
        }

        return $this->success($result['data']);
    }

    /**
     * 更新地址
     */
    public function update(): \think\Response
    {
        $this->auth();

        $id = (int) ($this->request->post('id') ?? 0);
        $data = $this->request->post();

        $result = $this->entity->update($id, $this->userId, $data);

        if (!$result['success']) {
            if (!empty($result['errors'])) {
                $errors = array_values($result['errors']);
                return $this->error($errors[0]);
            }
            return $this->error($result['msg'] ?? '更新失败');
        }

        return $this->success($result['data']);
    }

    /**
     * 删除地址
     */
    public function delete(): \think\Response
    {
        $this->auth();

        $id = (int) ($this->request->post('id') ?? 0);

        $result = $this->entity->delete($id, $this->userId);

        if (!$result['success']) {
            return $this->error($result['msg']);
        }

        return $this->success();
    }

    /**
     * 设置默认地址
     */
    public function setDefault(): \think\Response
    {
        $this->auth();

        $id = (int) ($this->request->post('id') ?? 0);

        $result = $this->entity->setDefault($id, $this->userId);

        if (!$result['success']) {
            return $this->error($result['msg']);
        }

        return $this->success();
    }
}
