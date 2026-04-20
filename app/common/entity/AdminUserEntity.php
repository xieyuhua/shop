<?php

declare(strict_types=1);

namespace app\common\entity;

use app\common\model\User;
use app\common\model\Shop;
use app\common\model\UserAddress;
use app\common\model\BalanceLog;
use app\common\model\PointsLog;
use think\model\concern\SoftDelete;

/**
 * 后台用户实体
 */
class AdminUserEntity extends BaseEntity
{
    use SoftDelete;

    protected $table = 'user';
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;

    protected $type = [
        'balance' => 'float',
        'points' => 'integer',
        'status' => 'integer',
        'level' => 'integer',
    ];

    // ========== 业务逻辑 ==========

    /**
     * 获取用户列表
     */
    public function getList(array $params): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $limit = min(50, max(1, (int) ($params['limit'] ?? 15)));
        $keyword = $params['keyword'] ?? '';
        $status = $params['status'] ?? '';

        $query = self::with(['shop'])->order('id', 'desc');

        if (!empty($keyword)) {
            $keyword = addcslashes($keyword, '%_');
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

        return [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ];
    }

    /**
     * 获取用户详情
     */
    public function getDetail(int $id): ?array
    {
        $user = self::with(['shop', 'addresses'])->find($id);
        return $user ? $user->toArray() : null;
    }

    /**
     * 设置用户状态
     */
    public function setStatus(int $id, int $status): array
    {
        $user = self::find($id);
        if (!$user) {
            return ['success' => false, 'msg' => '用户不存在'];
        }

        $user->status = $status;
        $user->save();

        return ['success' => true];
    }

    /**
     * 调整用户余额
     */
    public function adjustBalance(int $id, float $amount, string $type, string $remark, int $adminId): array
    {
        $user = self::find($id);
        if (!$user) {
            return ['success' => false, 'msg' => '用户不存在'];
        }

        if ($type === 'add') {
            $user->balance = $user->balance + $amount;

            $log = new BalanceLog();
            $log->user_id = $id;
            $log->change_type = BalanceLog::TYPE_INCOME;
            $log->balance = $amount;
            $log->description = '管理员调整：' . $remark;
            $log->source_type = 'admin';
            $log->source_id = $adminId;
            $log->save();
        } else {
            if ($user->balance < $amount) {
                return ['success' => false, 'msg' => '余额不足'];
            }
            $user->balance = $user->balance - $amount;

            $log = new BalanceLog();
            $log->user_id = $id;
            $log->change_type = BalanceLog::TYPE_EXPEND;
            $log->balance = -$amount;
            $log->description = '管理员调整：' . $remark;
            $log->source_type = 'admin';
            $log->source_id = $adminId;
            $log->save();
        }

        $user->save();

        return ['success' => true, 'data' => ['balance' => $user->balance]];
    }

    /**
     * 调整用户积分
     */
    public function adjustPoints(int $id, int $points, string $type, string $remark, int $adminId): array
    {
        $user = self::find($id);
        if (!$user) {
            return ['success' => false, 'msg' => '用户不存在'];
        }

        if ($type === 'add') {
            $user->points = $user->points + $points;

            $log = new PointsLog();
            $log->user_id = $id;
            $log->change_type = PointsLog::TYPE_INCOME;
            $log->points = $points;
            $log->description = '管理员调整：' . $remark;
            $log->source_type = 'admin';
            $log->source_id = $adminId;
            $log->save();
        } else {
            if ($user->points < $points) {
                return ['success' => false, 'msg' => '积分不足'];
            }
            $user->points = $user->points - $points;

            $log = new PointsLog();
            $log->user_id = $id;
            $log->change_type = PointsLog::TYPE_EXPEND;
            $log->points = -$points;
            $log->description = '管理员调整：' . $remark;
            $log->source_type = 'admin';
            $log->source_id = $adminId;
            $log->save();
        }

        $user->save();

        return ['success' => true, 'data' => ['points' => $user->points]];
    }
}
