<?php

declare(strict_types=1);

namespace app\admin\controller;

use app\common\controller\BaseController;
use app\common\entity\AdminUserEntity;

/**
 * 后台用户管理控制器 - 仅接收参数和返回结果
 */
class User extends BaseController
{
    private AdminUserEntity $userEntity;

    public function __construct()
    {
        parent::__construct();
        $this->userEntity = new AdminUserEntity();
    }

    /**
     * 用户列表
     */
    public function list(): \think\Response
    {
        $this->adminAuth();

        $params = [
            'page' => $this->request->get('page', 1),
            'limit' => $this->request->get('limit', 15),
            'keyword' => $this->request->get('keyword', ''),
            'status' => $this->request->get('status', ''),
        ];

        $result = $this->userEntity->getList($params);

        return $this->success($result);
    }

    /**
     * 用户详情
     */
    public function detail(): \think\Response
    {
        $this->adminAuth();

        $id = (int) $this->request->get('id', 0);

        $user = $this->userEntity->getDetail($id);

        if (!$user) {
            return $this->error('用户不存在');
        }

        return $this->success($user);
    }

    /**
     * 设置用户状态
     */
    public function setStatus(): \think\Response
    {
        $this->adminAuth();

        $id = (int) $this->request->post('id', 0);
        $status = (int) $this->request->post('status', 1);

        $result = $this->userEntity->setStatus($id, $status);

        if (!$result['success']) {
            return $this->error($result['msg']);
        }

        return $this->success();
    }

    /**
     * 调整用户余额
     */
    public function adjustBalance(): \think\Response
    {
        $this->adminAuth();

        $id = (int) $this->request->post('id', 0);
        $amount = (float) $this->request->post('amount', 0);
        $type = $this->request->post('type', 'add');
        $remark = $this->request->post('remark', '');

        $result = $this->userEntity->adjustBalance($id, $amount, $type, $remark, $this->adminId);

        if (!$result['success']) {
            return $this->error($result['msg']);
        }

        return $this->success($result['data']);
    }

    /**
     * 调整用户积分
     */
    public function adjustPoints(): \think\Response
    {
        $this->adminAuth();

        $id = (int) $this->request->post('id', 0);
        $points = (int) $this->request->post('points', 0);
        $type = $this->request->post('type', 'add');
        $remark = $this->request->post('remark', '');

        $result = $this->userEntity->adjustPoints($id, $points, $type, $remark, $this->adminId);

        if (!$result['success']) {
            return $this->error($result['msg']);
        }

        return $this->success($result['data']);
    }
}
