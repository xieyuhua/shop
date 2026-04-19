<?php

declare(strict_types=1);

namespace app\api\controller;

use app\common\controller\BaseController;
use app\common\entity\UserEntity;

/**
 * 用户控制器 - 仅接收参数和返回结果
 */
class User extends BaseController
{
    private UserEntity $userEntity;

    public function __construct()
    {
        parent::__construct();
        $this->userEntity = new UserEntity();
    }

    /**
     * 获取用户中心信息
     */
    public function center(): \think\Response
    {
        $this->auth();

        $userInfo = $this->userEntity->getInfo($this->userId);

        if (!$userInfo) {
            return $this->error('用户不存在');
        }

        // 获取订单统计
        $orderEntity = new \app\common\entity\OrderEntity();
        $orderCount = $orderEntity->getCount($this->userId);

        $userInfo['order_count'] = $orderCount['data'] ?? [];

        return $this->success($userInfo);
    }

    /**
     * 获取用户信息
     */
    public function info(): \think\Response
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
    public function update(): \think\Response
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
     * 获取余额日志
     */
    public function balanceLogs(): \think\Response
    {
        $this->auth();

        $page = (int) $this->request->get('page', 1);
        $limit = (int) $this->request->get('limit', 15);

        $logs = \app\common\model\BalanceLog::where('user_id', $this->userId)
            ->order('id', 'desc')
            ->page($page, $limit)
            ->select();

        $total = \app\common\model\BalanceLog::where('user_id', $this->userId)->count();

        return $this->success([
            'list' => $logs,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit),
        ]);
    }

    /**
     * 获取积分日志
     */
    public function pointsLogs(): \think\Response
    {
        $this->auth();

        $page = (int) $this->request->get('page', 1);
        $limit = (int) $this->request->get('limit', 15);

        $logs = \app\common\model\PointsLog::where('user_id', $this->userId)
            ->order('id', 'desc')
            ->page($page, $limit)
            ->select();

        $total = \app\common\model\PointsLog::where('user_id', $this->userId)->count();

        return $this->success([
            'list' => $logs,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit),
        ]);
    }
}
