<?php
declare(strict_types=1);

namespace app\repository;

use app\entity\CategoryEntity;

class CategoryRepository extends Repository
{
    protected $model = 'admin.Category';

    public function findByPid(int $pid): array
    {
        return CategoryEntity::where('pid', $pid)
            ->order('sort', 'asc')
            ->select()
            ->toArray();
    }

    public function findTopLevel(): array
    {
        return CategoryEntity::where('pid', 0)
            ->order('sort', 'asc')
            ->select()
            ->toArray();
    }

    public function getActiveList(): array
    {
        return CategoryEntity::where('status', 1)
            ->order('sort', 'asc')
            ->select()
            ->toArray();
    }

    public function getTree(): array
    {
        $all = $this->getActiveList();
        return $this->buildTree($all, 0);
    }

    protected function buildTree(array $list, int $pid = 0): array
    {
        $tree = [];
        foreach ($list as $item) {
            if ($item['pid'] == $pid) {
                $item['children'] = $this->buildTree($list, $item['id']);
                $tree[] = $item;
            }
        }
        return $tree;
    }
}