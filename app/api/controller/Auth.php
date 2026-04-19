<?php

declare(strict_types=1);

namespace app\api\controller;

use app\common\controller\BaseController;
use app\common\entity\UserEntity;
use app\common\library\JwtAuth;

/**
 * 认证控制器 - 仅接收参数和返回结果
 */
class Auth extends BaseController
{
    private UserEntity $userEntity;

    public function __construct()
    {
        parent::__construct();
        $this->userEntity = new UserEntity();
    }

    /**
     * 用户注册
     */
    public function register(): \think\Response
    {
        $data = $this->request->post();

        $result = $this->userEntity->register($data);

        if (!$result['success']) {
            return $this->error($result['errors']['username'] ?? $result['errors']['password'] ?? '注册失败');
        }

        return $this->success($result['data']);
    }

    /**
     * 用户登录
     */
    public function login(): \think\Response
    {
        $data = $this->request->post();

        $result = $this->userEntity->login($data);

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
        $token = $this->request->header('Authorization', '');

        $this->userEntity->logout($token);

        return $this->success();
    }

    /**
     * 获取用户信息
     */
    public function getUserInfo(): \think\Response
    {
        $this->auth();

        $result = $this->userEntity->getInfo($this->userId);

        if (!$result) {
            return $this->error('用户不存在');
        }

        return $this->success($result);
    }

    /**
     * 更新用户信息
     */
    public function updateUserInfo(): \think\Response
    {
        $this->auth();

        $data = $this->request->post();

        $result = $this->userEntity->updateInfo($this->userId, $data);

        if (!$result['success']) {
            return $this->error($result['msg']);
        }

        return $this->success($result['data']);
    }

    /**
     * 修改密码
     */
    public function changePassword(): \think\Response
    {
        $this->auth();

        $data = $this->request->post();

        $result = $this->userEntity->changePassword(
            $this->userId,
            $data['old_password'] ?? '',
            $data['new_password'] ?? ''
        );

        if (!$result['success']) {
            return $this->error($result['msg']);
        }

        return $this->success();
    }

    /**
     * 绑定手机号
     */
    public function bindMobile(): \think\Response
    {
        $this->auth();

        $data = $this->request->post();

        $result = $this->userEntity->bindMobile(
            $this->userId,
            $data['mobile'] ?? '',
            $data['code'] ?? ''
        );

        if (!$result['success']) {
            return $this->error($result['msg']);
        }

        return $this->success();
    }

    /**
     * 刷新 Token
     */
    public function refreshToken(): \think\Response
    {
        $token = $this->request->header('Authorization', '');

        $newToken = $this->userEntity->refreshToken($token);

        if (!$newToken) {
            return $this->error('Token刷新失败');
        }

        return $this->success(['token' => $newToken]);
    }
}
