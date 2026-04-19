<?php

declare(strict_types=1);

namespace app\common\library;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

/**
 * JWT 认证类 - 支持单点登录
 */
class JwtAuth
{
    private static ?string $secretKey = null;
    private static string $algorithm = 'HS256';
    private static int $expire = 604800; // 7天

    /**
     * 获取密钥
     */
    private static function getSecretKey(): string
    {
        if (self::$secretKey === null) {
            self::$secretKey = env('JWT.JWT_SECRET', 'your-secret-key-change-in-production');
        }
        return self::$secretKey;
    }

    /**
     * 设置密钥
     */
    public static function setSecretKey(string $key): void
    {
        self::$secretKey = $key;
    }

    /**
     * 设置过期时间（秒）
     */
    public static function setExpire(int $expire): void
    {
        self::$expire = $expire;
    }

    /**
     * 生成 Token (用户登录)
     */
    public static function generateUserToken(int $userId, int $shopId = 0, array $extra = []): string
    {
        $time = time();
        $payload = [
            'iss' => 'mall_b2b2c',
            'aud' => 'mall_user',
            'iat' => $time,
            'nbf' => $time,
            'exp' => $time + self::$expire,
            'type' => 'user',
            'user_id' => $userId,
            'shop_id' => $shopId,
            'extra' => $extra,
        ];

        return JWT::encode($payload, self::getSecretKey(), self::$algorithm);
    }

    /**
     * 生成 Admin Token
     */
    public static function generateAdminToken(int $adminId, int $roleId = 0, array $extra = []): string
    {
        $time = time();
        $payload = [
            'iss' => 'mall_b2b2c',
            'aud' => 'mall_admin',
            'iat' => $time,
            'nbf' => $time,
            'exp' => $time + self::$expire,
            'type' => 'admin',
            'admin_id' => $adminId,
            'role_id' => $roleId,
            'extra' => $extra,
        ];

        return JWT::encode($payload, self::getSecretKey(), self::$algorithm);
    }

    /**
     * 验证 Token
     */
    public static function verify(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key(self::getSecretKey(), self::$algorithm));
            return (array) $decoded;
        } catch (ExpiredException $e) {
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 从 Token 获取用户ID
     */
    public static function getUserId(string $token): ?int
    {
        $payload = self::verify($token);
        if (!$payload || ($payload['type'] ?? '') !== 'user') {
            return null;
        }
        return $payload['user_id'] ?? null;
    }

    /**
     * 从 Token 获取管理员ID
     */
    public static function getAdminId(string $token): ?int
    {
        $payload = self::verify($token);
        if (!$payload || ($payload['type'] ?? '') !== 'admin') {
            return null;
        }
        return $payload['admin_id'] ?? null;
    }

    /**
     * 获取店铺ID
     */
    public static function getShopId(string $token): ?int
    {
        $payload = self::verify($token);
        if (!$payload || ($payload['type'] ?? '') !== 'user') {
            return null;
        }
        return $payload['shop_id'] ?? null;
    }

    /**
     * 刷新 Token
     */
    public static function refresh(string $token): ?string
    {
        $payload = self::verify($token);
        if (!$payload) {
            return null;
        }

        if ($payload['type'] === 'user') {
            return self::generateUserToken(
                $payload['user_id'],
                $payload['shop_id'] ?? 0,
                $payload['extra'] ?? []
            );
        }

        if ($payload['type'] === 'admin') {
            return self::generateAdminToken(
                $payload['admin_id'],
                $payload['role_id'] ?? 0,
                $payload['extra'] ?? []
            );
        }

        return null;
    }

    /**
     * 获取 Token 剩余有效期
     */
    public static function getExpire(string $token): int
    {
        $payload = self::verify($token);
        if (!$payload) {
            return 0;
        }
        return max(0, ($payload['exp'] ?? 0) - time());
    }
}
