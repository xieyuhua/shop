<?php

declare(strict_types=1);

namespace app\admin\controller;

use app\common\entity\AdminUserEntity;

/**
 * 后台首页/登录控制器 - 仅接收参数和返回结果
 */
class Index extends BaseController
{
    private AdminUserEntity $entity;

    public function __construct()
    {
        parent::__construct();
        $this->entity = new AdminUserEntity();
    }

    /**
     * 登录
     */
    public function login(): \think\Response
    {
        $data = $this->request->post();

        $result = $this->entity->login($data);

        if (!$result['success']) {
            return $this->error($result['msg']);
        }

        return $this->success($result['data']);
    }

    /**
     * 退出登录
     */
    public function logout(): \think\Response
    {
        $token = $this->getAdminToken();

        $this->entity->logout($token);

        return $this->success();
    }

    /**
     * 获取管理员信息
     */
    public function info(): \think\Response
    {
        $this->adminAuth();

        $result = $this->entity->getInfo($this->adminId);

        if (!$result) {
            return $this->error('管理员不存在');
        }

        return $this->success($result);
    }

    /**
     * 修改密码
     */
    public function changePassword(): \think\Response
    {
        $this->adminAuth();

        $data = $this->request->post();

        $result = $this->entity->changePassword(
            $this->adminId,
            $data['old_password'] ?? '',
            $data['new_password'] ?? ''
        );

        if (!$result['success']) {
            return $this->error($result['msg']);
        }

        return $this->success();
    }

    /**
     * 更新管理员信息
     */
    public function updateInfo(): \think\Response
    {
        $this->adminAuth();

        $data = $this->request->post();

        $result = $this->entity->updateInfo($this->adminId, $data);

        if (!$result['success']) {
            return $this->error($result['msg']);
        }

        return $this->success($result['data']);
    }
}
