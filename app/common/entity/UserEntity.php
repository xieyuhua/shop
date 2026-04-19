<?php

declare(strict_types=1);

namespace app\common\entity;

use app\common\model\User as UserModel;
use app\common\library\JwtAuth;

/**
 * 用户实体 - 处理用户相关业务逻辑
 */
class UserEntity
{
    private UserModel $model;

    public function __construct(UserModel $model = null)
    {
        $this->model = $model ?? new UserModel();
    }

    /**
     * 用户注册
     */
    public function register(array $data): array
    {
        $errors = $this->validateRegister($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $user = new UserModel();
        $user->username = $data['username'];
        $user->nickname = $data['nickname'] ?? $data['username'];
        $user->password = $data['password'];
        $user->mobile = $data['mobile'] ?? '';
        $user->email = $data['email'] ?? '';
        $user->status = 1;
        $user->level = 1;
        $user->points = 0;
        $user->balance = 0;
        $user->save();

        $token = JwtAuth::generateUserToken($user->id);

        return [
            'success' => true,
            'data' => [
                'token' => $token,
                'user_id' => $user->id,
            ],
        ];
    }

    /**
     * 用户登录
     */
    public function login(array $data): array
    {
        $loginType = $data['login_type'] ?? 'username';
        $user = $this->findUserByLoginType($loginType, $data);

        if (!$user) {
            return ['success' => false, 'msg' => '用户不存在'];
        }

        if (!password_verify($data['password'], $user->password)) {
            return ['success' => false, 'msg' => '密码错误'];
        }

        if ($user->status != 1) {
            return ['success' => false, 'msg' => '账号已被禁用'];
        }

        // 更新登录信息
        $user->last_login_time = time();
        $user->last_login_ip = request()->ip();
        $user->save();

        $shopId = $user->shop ? $user->shop->id : 0;
        $token = JwtAuth::generateUserToken($user->id, $shopId);

        return [
            'success' => true,
            'data' => [
                'token' => $token,
                'user_id' => $user->id,
                'user_info' => $this->formatUserInfo($user),
            ],
        ];
    }

    /**
     * 退出登录
     */
    public function logout(string $token): bool
    {
        // JWT 无状态，客户端删除 token 即可
        return true;
    }

    /**
     * 刷新 Token
     */
    public function refreshToken(string $token): ?string
    {
        return JwtAuth::refresh($token);
    }

    /**
     * 获取用户信息
     */
    public function getInfo(int $userId): ?array
    {
        $user = UserModel::find($userId);
        if (!$user) {
            return null;
        }
        return $this->formatUserInfo($user);
    }

    /**
     * 更新用户信息
     */
    public function updateInfo(int $userId, array $data): array
    {
        $user = UserModel::find($userId);
        if (!$user) {
            return ['success' => false, 'msg' => '用户不存在'];
        }

        if (isset($data['nickname'])) {
            $user->nickname = $data['nickname'];
        }
        if (isset($data['avatar'])) {
            $user->avatar = $data['avatar'];
        }
        if (isset($data['gender'])) {
            $user->gender = (int) $data['gender'];
        }
        if (isset($data['birthday'])) {
            $user->birthday = strtotime($data['birthday']);
        }

        $user->save();

        return [
            'success' => true,
            'data' => $this->formatUserInfo($user),
        ];
    }

    /**
     * 修改密码
     */
    public function changePassword(int $userId, string $oldPassword, string $newPassword): array
    {
        if (strlen($newPassword) < 6) {
            return ['success' => false, 'msg' => '新密码长度不能少于6位'];
        }

        $user = UserModel::find($userId);
        if (!$user) {
            return ['success' => false, 'msg' => '用户不存在'];
        }

        if (!password_verify($oldPassword, $user->password)) {
            return ['success' => false, 'msg' => '原密码错误'];
        }

        $user->password = $newPassword;
        $user->save();

        return ['success' => true];
    }

    /**
     * 绑定手机号
     */
    public function bindMobile(int $userId, string $mobile, string $code): array
    {
        $exists = UserModel::where('mobile', encrypt_mobile($mobile))
            ->where('id', '<>', $userId)
            ->find();
        if ($exists) {
            return ['success' => false, 'msg' => '手机号已被绑定'];
        }

        $user = UserModel::find($userId);
        if (!$user) {
            return ['success' => false, 'msg' => '用户不存在'];
        }

        $user->mobile = $mobile;
        $user->save();

        return ['success' => true];
    }

    /**
     * 验证注册数据
     */
    private function validateRegister(array $data): array
    {
        $errors = [];

        if (!preg_match('/^[a-zA-Z0-9_]{4,20}$/', $data['username'] ?? '')) {
            $errors['username'] = '用户名必须为4-20位字母数字下划线';
        }

        if (strlen($data['password'] ?? '') < 6) {
            $errors['password'] = '密码长度不能少于6位';
        }

        $exists = UserModel::where('username', $data['username'])->find();
        if ($exists) {
            $errors['username'] = '用户名已存在';
        }

        if (!empty($data['mobile'])) {
            $mobileExists = UserModel::where('mobile', encrypt_mobile($data['mobile']))->find();
            if ($mobileExists) {
                $errors['mobile'] = '手机号已被使用';
            }
        }

        return $errors;
    }

    /**
     * 根据登录类型查找用户
     */
    private function findUserByLoginType(string $loginType, array $data): ?UserModel
    {
        switch ($loginType) {
            case 'mobile':
                if (empty($data['mobile'])) {
                    return null;
                }
                return UserModel::where('mobile', encrypt_mobile($data['mobile']))->find();

            case 'email':
                if (empty($data['email'])) {
                    return null;
                }
                return UserModel::where('email', $data['email'])->find();

            default:
                if (empty($data['username'])) {
                    return null;
                }
                return UserModel::where('username', $data['username'])->find();
        }
    }

    /**
     * 格式化用户信息
     */
    private function formatUserInfo(UserModel $user): array
    {
        return [
            'id' => $user->id,
            'username' => $user->username,
            'nickname' => $user->nickname,
            'avatar' => $user->avatar,
            'mobile' => $user->mobile,
            'email' => $user->email,
            'gender' => $user->gender,
            'birthday' => $user->birthday,
            'level' => $user->level,
            'points' => $user->points,
            'balance' => $user->balance,
        ];
    }
}
