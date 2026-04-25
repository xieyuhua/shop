<?php
declare(strict_types=1);

namespace app\service\admin;

use app\entity\UserEntity;
use think\Paginator;

class UserService extends \app\service\Service
{
    protected $model = 'admin.User';

    public function getList(int $page = 1, int $limit = 15, string $keyword = ''): Paginator
    {
        $query = UserEntity::order('id', 'desc');
        
        if ($keyword) {
            $query->where('username|mobile|email|nickname', 'like', "%{$keyword}%");
        }
        
        return $query->paginate([
            'page' => $page,
            'list_rows' => $limit,
        ]);
    }

    public function create(array $data): array
    {
        if (empty($data['mobile']) || empty($data['password'])) {
            return $this->error('手机号和密码不能为空');
        }
        
        if (UserEntity::where('mobile', $data['mobile'])->find()) {
            return $this->error('手机号已存在');
        }
        
        $user = new UserEntity();
        $user->save($data);
        
        return $this->result($user, '添加成功');
    }

    public function update(int $id, array $data): array
    {
        $user = UserEntity::find($id);
        if (!$user) {
            return $this->error('记录不存在');
        }
        
        if (isset($data['password']) && empty($data['password'])) {
            unset($data['password']);
        }
        
        $user->save($data);
        
        return $this->result($user, '更新成功');
    }

    public function delete(int $id): array
    {
        $user = UserEntity::find($id);
        if (!$user) {
            return $this->error('记录不存在');
        }
        
        $user->delete();
        
        return $this->result(null, '删除成功');
    }

    public function setStatus(int $id, int $status): array
    {
        $user = UserEntity::find($id);
        if (!$user) {
            return $this->error('记录不存在');
        }
        
        $user->save(['status' => $status]);
        
        return $this->result(null, '操作成功');
    }

    public function getInfo(int $id): UserEntity|null
    {
        return UserEntity::find($id);
    }

    public function getUserByMobile(string $mobile): UserEntity|null
    {
        return UserEntity::where('mobile', $mobile)->find();
    }

    public function updateBalance(int $id, float $amount, string $remark = ''): array
    {
        $user = UserEntity::find($id);
        if (!$user) {
            return $this->error('用户不存在');
        }
        
        $beforeBalance = $user->balance;
        $user->balance += $amount;
        $user->save();
        
        \app\entity\WalletLogEntity::create([
            'user_id' => $id,
            'type' => $amount > 0 ? 1 : 2,
            'amount' => abs($amount),
            'before_balance' => $beforeBalance,
            'after_balance' => $user->balance,
            'remark' => $remark,
        ]);
        
        return $this->result(null, '操作成功');
    }

    public function updatePoints(int $id, int $points, string $remark = ''): array
    {
        $user = UserEntity::find($id);
        if (!$user) {
            return $this->error('用户不存在');
        }
        
        $user->points += $points;
        $user->save();
        
        return $this->result(null, '操作成功');
    }
}