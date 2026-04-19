<?php

declare(strict_types=1);

namespace app\common\entity;

use app\common\model\Admin as AdminModel;
use app\common\library\JwtAuth;

/**
 * 管理员实体 - 处理管理员相关业务逻辑
 */
class AdminUserEntity
{
    /**
     * 管理员登录
     */
    public function login(array $data): array
    {
        if (empty($data['username']) || empty($data['password'])) {
            return ['success' => false, 'msg' => '请输入用户名和密码'];
        }

        $admin = AdminModel::where('username', $data['username'])->find();

        if (!$admin) {
            return ['success' => false, 'msg' => '管理员不存在'];
        }

        if (!password_verify($data['password'], $admin->password)) {
            return ['success' => false, 'msg' => '密码错误'];
        }

        if ($admin->status != 1) {
            return ['success' => false, 'msg' => '账号已被禁用'];
        }

        // 更新登录信息
        $admin->last_login_time = time();
        $admin->last_login_ip = request()->ip();
        $admin->save();

        $token = JwtAuth::generateAdminToken($admin->id, $admin->role_id);

        return [
            'success' => true,
            'data' => [
                'token' => $token,
                'admin_id' => $admin->id,
                'admin_info' => [
                    'id' => $admin->id,
                    'username' => $admin->username,
                    'nickname' => $admin->nickname,
                    'avatar' => $admin->avatar,
                    'role_id' => $admin->role_id,
                ],
            ],
        ];
    }

    /**
     * 退出登录
     */
    public function logout(string $token): bool
    {
        return true;
    }

    /**
     * 获取管理员信息
     */
    public function getInfo(int $adminId): ?array
    {
        $admin = AdminModel::find($adminId);
        if (!$admin) {
            return null;
        }
        return [
            'id' => $admin->id,
            'username' => $admin->username,
            'nickname' => $admin->nickname,
            'avatar' => $admin->avatar,
            'role_id' => $admin->role_id,
        ];
    }

    /**
     * 修改密码
     */
    public function changePassword(int $adminId, string $oldPassword, string $newPassword): array
    {
        if (strlen($newPassword) < 6) {
            return ['success' => false, 'msg' => '新密码长度不能少于6位'];
        }

        $admin = AdminModel::find($adminId);
        if (!$admin) {
            return ['success' => false, 'msg' => '管理员不存在'];
        }

        if (!password_verify($oldPassword, $admin->password)) {
            return ['success' => false, 'msg' => '原密码错误'];
        }

        $admin->password = $newPassword;
        $admin->save();

        return ['success' => true];
    }

    /**
     * 更新管理员信息
     */
    public function updateInfo(int $adminId, array $data): array
    {
        $admin = AdminModel::find($adminId);
        if (!$admin) {
            return ['success' => false, 'msg' => '管理员不存在'];
        }

        if (isset($data['nickname'])) {
            $admin->nickname = $data['nickname'];
        }
        if (isset($data['avatar'])) {
            $admin->avatar = $data['avatar'];
        }

        $admin->save();

        return [
            'success' => true,
            'data' => [
                'id' => $admin->id,
                'username' => $admin->username,
                'nickname' => $admin->nickname,
                'avatar' => $admin->avatar,
            ],
        ];
    }
}
