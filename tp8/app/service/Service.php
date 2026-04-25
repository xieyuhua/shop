<?php
declare(strict_types=1);

namespace app\service;

use think\Model;

abstract class Service
{
    protected $model;

    protected function getModel(string $name = '')
    {
        $model = $name ?: $this->model;
        return model($model);
    }

    protected function success($data = null, $msg = 'success'): array
    {
        return ['code' => 200, 'msg' => $msg, 'data' => $data];
    }

    protected function error($msg = 'error', $code = 400): array
    {
        return ['code' => $code, 'msg' => $msg, 'data' => null];
    }

    protected function paginate($query, int $page, int $limit): array
    {
        $list = $query->page($page, $limit)->select();
        $total = $query->count();
        
        return [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ];
    }
}