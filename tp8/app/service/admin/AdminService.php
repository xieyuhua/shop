<?php
declare(strict_types=1);

namespace app\service\admin;

use app\entity\AdminEntity;
use think\Paginator;

class AdminService extends \app\service\Service
{
    protected $model = 'admin.Admin';

    public function login(string $username, string $password): array
    {
        $admin = AdminEntity::where('username', $username)->find();
        
        if (!$admin) {
            return $this->error('用户不存在');
        }
        
        if ($admin->status == 0) {
            return $this->error('账户已被禁用');
        }
        
        if (!$admin->verifyPassword($password)) {
            return $this->error('密码错误');
        }
        
        $admin->login_ip = request()->ip();
        $admin->login_time = date('Y-m-d H:i:s');
        $admin->save();
        
        session('admin', $admin->safeData());
        
        return $this->result($admin->safeData(), '登录成功');
    }

    public function logout(): array
    {
        session('admin', null);
        return $this->result(null, '已退出登录');
    }

    public function getList(int $page = 1, int $limit = 15, string $keyword = ''): Paginator
    {
        $query = AdminEntity::order('id', 'desc');
        
        if ($keyword) {
            $query->where('username|nickname', 'like', "%{$keyword}%");
        }
        
        return $query->paginate([
            'page' => $page,
            'list_rows' => $limit,
        ]);
    }

    public function create(array $data): array
    {
        if (AdminEntity::where('username', $data['username'] ?? '')->find()) {
            return $this->error('用户名已存在');
        }
        
        $admin = new AdminEntity();
        $admin->save($data);
        
        return $this->result($admin, '创建成功');
    }

    public function update(int $id, array $data): array
    {
        $admin = AdminEntity::find($id);
        if (!$admin) {
            return $this->error('记录不存在');
        }
        
        if (isset($data['password']) && empty($data['password'])) {
            unset($data['password']);
        }
        
        $admin->save($data);
        
        return $this->result($admin, '更新成功');
    }

    public function delete(int $id): array
    {
        $admin = AdminEntity::find($id);
        if (!$admin) {
            return $this->error('记录不存在');
        }
        
        if ($admin->username === 'admin') {
            return $this->error('超级管理员不能删除');
        }
        
        $admin->delete();
        
        return $this->result(null, '删除成功');
    }

    public function setStatus(int $id, int $status): array
    {
        $admin = AdminEntity::find($id);
        if (!$admin) {
            return $this->error('记录不存在');
        }
        
        $admin->save(['status' => $status]);
        
        return $this->result(null, '操作成功');
    }

    public function getInfo(int $id): AdminEntity|null
    {
        return AdminEntity::find($id);
    }
}