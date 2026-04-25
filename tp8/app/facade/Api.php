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
 * @method static mixed get(string $group)
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
 * @method static success($data = null, string $msg = 'success')
 * @method static error(string $msg = 'error', int $code = 400)
 * @method static notFound(string $msg = '资源不存在')
 * @method static unauthorized(string $msg = '未授权')
 * @method static forbidden(string $msg = '没有权限')
 * @method static validateError(string $msg = '数据验证失败')
 * @method static serverError(string $msg = '服务器错误')
 * @method static paginate(array $list, int $total, int $page, int $limit)
 * @method static tree(array $list, int $pid = 0, string $pk = 'id', string $pidKey = 'pid')
 */
class ApiResult extends Facade
{
    protected static function getFacadeClass()
    {
        return \app\api\ApiResponse::class;
    }
}