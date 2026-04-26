<?php
declare(strict_types=1);

namespace app\facade;

use think\Facade;

/**
 * @method static mixed get(string $key, $default = null)
 * @method static bool set(string $key, $value, int $ttl = 0)
 * @method static bool has(string $key): bool
 * @method static bool inc(string $key, int $step = 1)
 * @method static bool dec(string $key, int $step = 1)
 * @method static bool pull(string $key, $default = null)
 * @method static bool forget(string $key): bool
 * @method static bool clear(string $tag = ''): bool
 * @method static mixed remember(string $key, callable $callback, int $ttl = 3600)
 */
class Cache extends Facade
{
    protected static function getFacadeClass()
    {
        return \app\service\CacheService::class;
    }
}

/**
 * @method static array get(string $group)
 * @method static array getList(string $group)
 * @method static array getOptions(string $group)
 * @method static void refresh(string $group = '')
 */
class Dict extends Facade
{
    protected static function getFacadeClass()
    {
        return \app\service\DictService::class;
    }
}

/**
 * @method static array login(array $data)
 * @method static array logout()
 * @method static array getInfo(int $adminId)
 */
class Auth extends Facade
{
    protected static function getFacadeClass()
    {
        return \app\service\AuthService::class;
    }
}

/**
 * @method static array getValue(string $name, string $default = '')
 * @method static array getGroupConfig(string $group)
 */
class Config extends Facade
{
    protected static function getFacadeClass()
    {
        return \app\service\ConfigService::class;
    }
}

/**
 * @method static string generateOrderNo(int $userId = 0)
 * @method static string generateUniqueId(string $prefix = '')
 * @method static string mobileMask(string $mobile)
 * @method static string emailMask(string $email)
 * @method static int amount(float $amount)
 * @method static float money(int $amount)
 * @method static string formatBytes(int $bytes)
 * @method static string getClientType()
 * @method static bool isMobile()
 * @method static bool isWechat()
 * @method static bool isAjax()
 */
class Helper extends Facade
{
    protected static function getFacadeClass()
    {
        return \app\service\HelperService::class;
    }
}