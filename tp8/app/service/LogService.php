<?php
declare(strict_types=1);

namespace app\service;

use think\facade\Log;

class LogService
{
    const LOGIN = 1;
    const LOGOUT = 2;
    const CREATE = 3;
    const UPDATE = 4;
    const DELETE = 5;
    const OTHER = 10;

    public static function record(string $content, int $type = self::OTHER, array $extra = []): void
    {
        $data = [
            'type' => $type,
            'content' => $content,
            'admin_id' => $extra['admin_id'] ?? 0,
            'admin_name' => $extra['admin_name'] ?? '',
            'ip' => $extra['ip'] ?? request()->ip(),
            'url' => $extra['url'] ?? request()->url(true),
        ];

        $typeMap = [
            self::LOGIN => '登录',
            self::LOGOUT => '退出',
            self::CREATE => '创建',
            self::UPDATE => '更新',
            self::DELETE => '删除',
            self::OTHER => '操作',
        ];

        $typeName = $typeMap[$type] ?? '操作';

        Log::info("{$typeName}: {$content}", $data);
    }

    public static function error(string $content, array $extra = []): void
    {
        $data = [
            'admin_id' => $extra['admin_id'] ?? 0,
            'admin_name' => $extra['admin_name'] ?? '',
            'ip' => $extra['ip'] ?? request()->ip(),
            'url' => $extra['url'] ?? request()->url(true),
        ];

        Log::error($content, $data);
    }

    public static function access(string $content, array $extra = []): void
    {
        $data = [
            'admin_id' => $extra['admin_id'] ?? 0,
            'admin_name' => $extra['admin_name'] ?? '',
            'ip' => $extra['ip'] ?? request()->ip(),
            'url' => $extra['url'] ?? request()->url(true),
            'method' => $extra['method'] ?? request()->method(),
            'param' => $extra['param'] ?? request()->param(),
        ];

        Log::info($content, $data);
    }
}