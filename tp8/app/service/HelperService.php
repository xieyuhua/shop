<?php
declare(strict_types=1);

namespace app\service;

use think\facade\Db;

class HelperService
{
    public static function generateOrderNo(int $userId = 0): string
    {
        $prefix = date('YmdHis');
        $random = mt_rand(1000, 9999);
        $userPrefix = str_pad($userId % 1000, 3, '0', STR_PAD_LEFT);
        
        return $prefix . $random . $userPrefix;
    }

    public static function generateUniqueId(string $prefix = ''): string
    {
        $uuid = bin2hex(random_bytes(16));
        
        if ($prefix) {
            return $prefix . '_' . $uuid;
        }
        
        return $uuid;
    }

    public static function generateRandomString(int $length = 16): string
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $result = '';
        
        for ($i = 0; $i < $length; $i++) {
            $result .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        
        return $result;
    }

    public static function mobileMask(string $mobile): string
    {
        if (empty($mobile)) {
            return '';
        }
        
        return preg_replace('/(\d{3})\d{4}(\d{4})/', '$1****$2', $mobile);
    }

    public static function emailMask(string $email): string
    {
        if (empty($email)) {
            return '';
        }
        
        $parts = explode('@', $email);
        $name = $parts[0];
        
        if (strlen($name) <= 2) {
            $mask = str_repeat('*', strlen($name));
        } else {
            $mask = $name[0] . str_repeat('*', strlen($name) - 2) . $name[-1];
        }
        
        return $mask . '@' . ($parts[1] ?? '');
    }

    public static function idCardMask(string $idCard): string
    {
        if (empty($idCard)) {
            return '';
        }
        
        return preg_replace('/(\d{6})\d{8}(\d{4})/', '$1********$2', $idCard);
    }

    public static function amount(float $amount): int
    {
        return (int)round($amount * 100);
    }

    public static function money(int $amount): float
    {
        return round($amount / 100, 2);
    }

    public static function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public static function parseUrl(string $url): array
    {
        $parsed = parse_url($url);
        
        return [
            'scheme' => $parsed['scheme'] ?? 'http',
            'host' => $parsed['host'] ?? '',
            'port' => $parsed['port'] ?? 80,
            'path' => $parsed['path'] ?? '/',
            'query' => $parsed['query'] ?? '',
            'fragment' => $parsed['fragment'] ?? ''
        ];
    }

    public static function buildUrl(string $path, array $params = []): string
    {
        $url = $path;
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        return $url;
    }

    public static function getClientType(): string
    {
        $agent = request()->header('user-agent', '');
        
        if (preg_match('/MicroMessenger/i', $agent)) {
            return 'wechat';
        }
        
        if (preg_match('/AlipayClient/i', $agent)) {
            return 'alipay';
        }
        
        if (preg_match('/Mobile/i', $agent)) {
            return 'mobile';
        }
        
        return 'web';
    }

    public static function isMobile(): bool
    {
        $agent = request()->header('user-agent', '');
        
        return preg_match('/(Mobile|iPhone|iPad|Android)/i', $agent);
    }

    public static function isWechat(): bool
    {
        $agent = request()->header('user-agent', '');
        
        return preg_match('/MicroMessenger/i', $agent);
    }

    public static function isAjax(): bool
    {
        return request()->header('X-Requested-With') === 'XMLHttpRequest';
    }
}