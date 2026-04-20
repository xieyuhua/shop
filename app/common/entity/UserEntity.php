<?php

declare(strict_types=1);

namespace app\common\entity;

use app\common\library\JwtAuth;
use app\common\model\UserAddress;
use think\model\concern\SoftDelete;

/**
 * 用户实体
 */
class UserEntity extends BaseEntity
{
    use SoftDelete;

    protected $table = 'user';
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;

    protected $type = [
        'balance' => 'float',
        'points' => 'integer',
        'status' => 'integer',
        'gender' => 'integer',
        'level' => 'integer',
    ];

    // ========== 修改器 ==========

    public function setPasswordAttr($value)
    {
        return password_hash($value, PASSWORD_DEFAULT);
    }

    public function setMobileAttr($value)
    {
        return encrypt_mobile($value);
    }

    public function getMobileAttr($value)
    {
        return $value ? decrypt_mobile($value) : '';
    }

    public function getAvatarAttr($value)
    {
        return $value ?: '/static/images/avatar.png';
    }

    // ========== 业务逻辑 ==========

    /**
     * 用户注册
     */
    public function register(array $data): array
    {
        $errors = $this->validateRegister($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $this->username = $data['username'];
        $this->nickname = $data['nickname'] ?? $data['username'];
        $this->password = $data['password'];
        $this->mobile = $data['mobile'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->status = 1;
        $this->level = 1;
        $this->points = 0;
        $this->balance = 0;
        $this->save();

        $token = JwtAuth::generateUserToken($this->id);

        return [
            'success' => true,
            'data' => [
                'token' => $token,
                'user_id' => $this->id,
            ],
        ];
    }

    /**
     * 用户登录
     */
    public function login(array $data): array
    {
        $loginType = $data['login_type'] ?? 'username';
        $user = $this->findByLoginType($loginType, $data);

        if (!$user) {
            return ['success' => false, 'msg' => '用户不存在'];
        }

        if (!password_verify($data['password'], $user->password)) {
            return ['success' => false, 'msg' => '密码错误'];
        }

        if ($user->status != 1) {
            return ['success' => false, 'msg' => '账号已被禁用'];
        }

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
                'user_info' => $this->formatInfo($user),
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
        $user = self::find($userId);
        if (!$user) {
            return null;
        }
        return $this->formatInfo($user);
    }

    /**
     * 更新用户信息
     */
    public function updateInfo(int $userId, array $data): array
    {
        $user = self::find($userId);
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
            'data' => $this->formatInfo($user),
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

        $user = self::find($userId);
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
        $exists = self::where('mobile', encrypt_mobile($mobile))
            ->where('id', '<>', $userId)
            ->find();
        if ($exists) {
            return ['success' => false, 'msg' => '手机号已被绑定'];
        }

        $user = self::find($userId);
        if (!$user) {
            return ['success' => false, 'msg' => '用户不存在'];
        }

        $user->mobile = $mobile;
        $user->save();

        return ['success' => true];
    }

    // ========== 私有方法 ==========

    private function validateRegister(array $data): array
    {
        $errors = [];

        if (!preg_match('/^[a-zA-Z0-9_]{4,20}$/', $data['username'] ?? '')) {
            $errors['username'] = '用户名必须为4-20位字母数字下划线';
        }

        if (strlen($data['password'] ?? '') < 6) {
            $errors['password'] = '密码长度不能少于6位';
        }

        $exists = self::where('username', $data['username'])->find();
        if ($exists) {
            $errors['username'] = '用户名已存在';
        }

        if (!empty($data['mobile'])) {
            $mobileExists = self::where('mobile', encrypt_mobile($data['mobile']))->find();
            if ($mobileExists) {
                $errors['mobile'] = '手机号已被使用';
            }
        }

        return $errors;
    }

    private function findByLoginType(string $loginType, array $data): ?self
    {
        return match ($loginType) {
            'mobile' => !empty($data['mobile']) ? self::where('mobile', encrypt_mobile($data['mobile']))->find() : null,
            'email' => !empty($data['email']) ? self::where('email', $data['email'])->find() : null,
            default => !empty($data['username']) ? self::where('username', $data['username'])->find() : null,
        };
    }

    private function formatInfo(self $user): array
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
