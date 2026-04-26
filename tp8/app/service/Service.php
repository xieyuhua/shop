<?php
declare(strict_types=1);

namespace app\service;

use think\db\BaseQuery as Query;
use think\Model;

abstract class Service
{
    protected string $model = '';
    protected Model|null $instance = null;

    protected function getModel(string $name = ''): string
    {
        return $name ?: $this->model;
    }

    protected function db(string $name = ''): Query
    {
        return db($this->getModel($name));
    }

    protected function find(int $id): ?Model
    {
        return db($this->model)->find($id);
    }

    protected function findOrFail(int $id): Model
    {
        $model = $this->find($id);
        if (!$model) {
            throw new \RuntimeException($this->model . ' not found');
        }
        return $model;
    }

    protected function select(array $where = []): array
    {
        return db($this->model)->where($where)->select()->toArray();
    }

    protected function paginate(Query $query, int $page = 1, int $limit = 15): array
    {
        $clone = clone $query;
        $total = $clone->count();
        $list = $query->page($page, $limit)->select()->toArray();

        return [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ];
    }

    protected function create(array $data): Model
    {
        $model = db($this->model);
        $model->save($data);
        return $model;
    }

    protected function update(int $id, array $data): Model
    {
        $model = $this->findOrFail($id);
        $model->save($data);
        return $model;
    }

    protected function delete(int $id): bool
    {
        return db($this->model)->where('id', $id)->delete() > 0;
    }

    protected function exists(array $where): bool
    {
        return db($this->model)->where($where)->find() !== null;
    }

    protected function getByField(string $field, mixed $value): ?Model
    {
        return db($this->model)->where($field, $value)->find();
    }

    protected function count(array $where = []): int
    {
        return db($this->model)->where($where)->count();
    }

    protected function success(mixed $data = null, string $msg = 'success'): array
    {
        return ['code' => 200, 'msg' => $msg, 'data' => $data];
    }

    protected function error(string $msg = 'error', int $code = 400): array
    {
        return ['code' => $code, 'msg' => $msg, 'data' => null];
    }

    protected function result(mixed $data, string $msg = 'success', int $code = 200): array
    {
        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }
}