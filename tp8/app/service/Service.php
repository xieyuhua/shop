<?php
declare(strict_types=1);

namespace app\service;

use think\db\BaseQuery;
use think\Paginator;

abstract class Service
{
    protected $model;
    protected $pk = 'id';

    protected function getModel(string $name = '')
    {
        $model = $name ?: $this->model;
        return model($model);
    }

    protected function where(array $where = []): BaseQuery
    {
        $query = $this->getModel();
        foreach ($where as $condition) {
            if (count($condition) >= 2) {
                $field = $condition[0];
                $op = $condition[1] ?? '=';
                $value = $condition[2] ?? null;
                $logic = $condition[3] ?? 'AND';
                $query = $query->where($field, $op, $value, $logic);
            }
        }
        return $query;
    }

    protected function order(string $field, string $order = 'desc'): BaseQuery
    {
        return $this->getModel()->order($field, $order);
    }

    protected function paginate(int $page, int $listRows, array $config = []): Paginator
    {
        return $this->getModel()->paginate([
            'page' => $page,
            'list_rows' => $listRows,
        ]);
    }

    protected function result(mixed $data, string $msg = 'success'): array
    {
        return ['code' => 200, 'msg' => $msg, 'data' => $data];
    }

    protected function error(string $msg = 'error'): array
    {
        return ['code' => 400, 'msg' => $msg];
    }

    protected function validate(array $data, array $rules, array $message = []): bool|array
    {
        try {
            $validate = new \think\Validate($rules, $message);
            if (!$validate->check($data)) {
                return $this->error($validate->getError());
            }
            return true;
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}