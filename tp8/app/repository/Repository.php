<?php
declare(strict_types=1);

namespace app\repository;

use think\db\BaseQuery;

abstract class Repository
{
    protected $model;

    protected function getModel(): BaseQuery
    {
        return model($this->model);
    }

    protected function getPk(): string
    {
        return 'id';
    }

    public function find(int $id): ?object
    {
        return $this->getModel()->find($id);
    }

    public function select(array $where = []): array
    {
        $query = $this->getModel();
        foreach ($where as $condition) {
            if (count($condition) >= 2) {
                $field = $condition[0];
                $op = $condition[1] ?? '=';
                $value = $condition[2] ?? null;
                $query = $query->where($field, $op, $value);
            }
        }
        return $query->select()->toArray();
    }

    public function all(): array
    {
        return $this->getModel()->select()->toArray();
    }

    public function create(array $data): object
    {
        return $this->getModel()->create($data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->getModel()->where($this->getPk(), $id)->update($data) !== false;
    }

    public function delete(int $id): bool
    {
        return $this->getModel()->where($this->getPk(), $id)->delete() > 0;
    }

    public function count(array $where = []): int
    {
        $query = $this->getModel();
        foreach ($where as $condition) {
            if (count($condition) >= 2) {
                $field = $condition[0];
                $op = $condition[1] ?? '=';
                $value = $condition[2] ?? null;
                $query = $query->where($field, $op, $value);
            }
        }
        return $query->count();
    }

    public function exists(array $where = []): bool
    {
        return $this->count($where) > 0;
    }
}