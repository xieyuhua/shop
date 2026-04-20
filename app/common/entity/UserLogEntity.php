<?php

declare(strict_types=1);

namespace app\common\entity;

use app\common\model\BalanceLog;
use app\common\model\PointsLog;
use app\common\model\User;

/**
 * 用户日志实体
 */
class UserLogEntity
{
    /**
     * 获取余额日志
     */
    public function getBalanceLogs(int $userId, int $page = 1, int $limit = 15): array
    {
        $page = max(1, $page);
        $limit = min(50, max(1, $limit));

        $query = BalanceLog::where('user_id', $userId)->order('id', 'desc');
        $total = $query->count();
        $list = $query->page($page, $limit)->select();

        return [
            'list' => $list->toArray(),
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit),
        ];
    }

    /**
     * 获取积分日志
     */
    public function getPointsLogs(int $userId, int $page = 1, int $limit = 15): array
    {
        $page = max(1, $page);
        $limit = min(50, max(1, $limit));

        $query = PointsLog::where('user_id', $userId)->order('id', 'desc');
        $total = $query->count();
        $list = $query->page($page, $limit)->select();

        return [
            'list' => $list->toArray(),
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit),
        ];
    }

    /**
     * 添加余额变动
     */
    public function addBalanceLog(int $userId, float $amount, string $type, string $remark = '', int $relateId = 0): bool
    {
        $log = new BalanceLog();
        $log->user_id = $userId;
        $log->type = $type;
        $log->amount = $amount;
        $log->balance = $this->getUserBalance($userId) + $amount;
        $log->remark = $remark;
        $log->relate_id = $relateId;
        return $log->save();
    }

    /**
     * 添加积分变动
     */
    public function addPointsLog(int $userId, int $points, string $type, string $remark = '', int $relateId = 0): bool
    {
        $log = new PointsLog();
        $log->user_id = $userId;
        $log->type = $type;
        $log->points = $points;
        $log->balance = $this->getUserPoints($userId) + $points;
        $log->remark = $remark;
        $log->relate_id = $relateId;
        return $log->save();
    }

    private function getUserBalance(int $userId): float
    {
        $user = User::find($userId);
        return $user ? (float) $user->balance : 0;
    }

    private function getUserPoints(int $userId): int
    {
        $user = User::find($userId);
        return $user ? (int) $user->points : 0;
    }
}
