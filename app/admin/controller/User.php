<?php

declare(strict_types=1);

namespace app\admin\controller;

use app\common\model\User as UserModel;

/**
 * 后台用户管理控制器 - 仅接收参数和返回结果
 */
class User extends BaseController
{
    /**
     * 用户列表
     */
    public function list(): \think\Response
    {
        $this->adminAuth();

        $page = (int) $this->request->get('page', 1);
        $limit = (int) $this->request->get('limit', 15);
        $keyword = $this->request->get('keyword', '');
        $status = $this->request->get('status', '');

        $query = UserModel::with(['shop'])->order('id', 'desc');

        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('username', 'like', '%' . $keyword . '%')
                    ->whereOr('nickname', 'like', '%' . $keyword . '%')
                    ->whereOr('mobile', 'like', '%' . $keyword . '%');
            });
        }

        if ($status !== '') {
            $query->where('status', (int) $status);
        }

        $total = $query->count();
        $list = $query->page($page, $limit)->select();

        return $this->success([
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ]);
    }

    /**
     * 用户详情
     */
    public function detail(): \think\Response
    {
        $this->adminAuth();

        $id = (int) $this->request->get('id', 0);

        $user = UserModel::with(['shop', 'addresses'])->find($id);

        if (!$user) {
            return $this->error('用户不存在');
        }

        return $this->success($user);
    }

    /**
     * 更新用户状态
     */
    public function setStatus(): \think\Response
    {
        $this->adminAuth();

        $id = (int) $this->request->post('id', 0);
        $status = (int) $this->request->post('status', 1);

        $user = UserModel::find($id);

        if (!$user) {
            return $this->error('用户不存在');
        }

        $user->status = $status;
        $user->save();

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
        $type = $this->request->post('type', 'add'); // add 或 reduce
        $remark = $this->request->post('remark', '');

        $user = UserModel::find($id);

        if (!$user) {
            return $this->error('用户不存在');
        }

        if ($type === 'add') {
            $user->balance = $user->balance + $amount;

            $log = new \app\common\model\BalanceLog();
            $log->user_id = $id;
            $log->change_type = \app\common\model\BalanceLog::TYPE_INCOME;
            $log->balance = $amount;
            $log->description = '管理员调整：' . $remark;
            $log->source_type = 'admin';
            $log->source_id = $this->adminId;
            $log->create_time = time();
            $log->save();
        } else {
            if ($user->balance < $amount) {
                return $this->error('余额不足');
            }
            $user->balance = $user->balance - $amount;

            $log = new \app\common\model\BalanceLog();
            $log->user_id = $id;
            $log->change_type = \app\common\model\BalanceLog::TYPE_EXPEND;
            $log->balance = -$amount;
            $log->description = '管理员调整：' . $remark;
            $log->source_type = 'admin';
            $log->source_id = $this->adminId;
            $log->create_time = time();
            $log->save();
        }

        $user->save();

        return $this->success(['balance' => $user->balance]);
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

        $user = UserModel::find($id);

        if (!$user) {
            return $this->error('用户不存在');
        }

        if ($type === 'add') {
            $user->points = $user->points + $points;

            $log = new \app\common\model\PointsLog();
            $log->user_id = $id;
            $log->change_type = \app\common\model\PointsLog::TYPE_INCOME;
            $log->points = $points;
            $log->description = '管理员调整：' . $remark;
            $log->source_type = 'admin';
            $log->source_id = $this->adminId;
            $log->create_time = time();
            $log->save();
        } else {
            if ($user->points < $points) {
                return $this->error('积分不足');
            }
            $user->points = $user->points - $points;

            $log = new \app\common\model\PointsLog();
            $log->user_id = $id;
            $log->change_type = \app\common\model\PointsLog::TYPE_EXPEND;
            $log->points = -$points;
            $log->description = '管理员调整：' . $remark;
            $log->source_type = 'admin';
            $log->source_id = $this->adminId;
            $log->create_time = time();
            $log->save();
        }

        $user->save();

        return $this->success(['points' => $user->points]);
    }
}
