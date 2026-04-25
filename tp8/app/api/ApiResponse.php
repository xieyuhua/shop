<?php
declare(strict_types=1);

namespace app\api;

use think\Response;

class ApiResponse
{
    public static function success($data = null, string $msg = 'success'): Response
    {
        return json([
            'code' => 200,
            'msg' => $msg,
            'data' => $data,
            'time' => time(),
        ]);
    }

    public static function error(string $msg = 'error', int $code = 400): Response
    {
        return json([
            'code' => $code,
            'msg' => $msg,
            'data' => null,
            'time' => time(),
        ]);
    }

    public static function notFound(string $msg = '资源不存在'): Response
    {
        return self::error($msg, 404);
    }

    public static function unauthorized(string $msg = '未授权'): Response
    {
        return self::error($msg, 401);
    }

    public static function forbidden(string $msg = '没有权限'): Response
    {
        return self::error($msg, 403);
    }

    public static function validateError(string $msg = '数据验证失败'): Response
    {
        return self::error($msg, 422);
    }

    public static function serverError(string $msg = '服务器错误'): Response
    {
        return self::error($msg, 500);
    }

    public static function paginate(array $list, int $total, int $page, int $limit): Response
    {
        return self::success([
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit),
        ]);
    }

    public static function tree(array $list, int $pid = 0, string $pk = 'id', string $pidKey = 'pid'): Response
    {
        return self::success(self::buildTree($list, $pid, $pk, $pidKey));
    }

    private static function buildTree(array $list, int $pid = 0, string $pk = 'id', string $pidKey = 'pid'): array
    {
        $tree = [];
        foreach ($list as $item) {
            if ($item[$pidKey] == $pid) {
                $children = self::buildTree($list, $item[$pk], $pk, $pidKey);
                if ($children) {
                    $item['children'] = $children;
                }
                $tree[] = $item;
            }
        }
        return $tree;
    }
}