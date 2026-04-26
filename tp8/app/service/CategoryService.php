<?php
declare(strict_types=1);

namespace app\service;

use app\model\admin\CategoryModel;

class CategoryService extends Service
{
    protected string $model = 'category';

    public function list(array $params = []): array
    {
        $keyword = $params['keyword'] ?? '';
        $status = $params['status'] ?? '';

        $query = db('category')->order('sort', 'asc');

        if ($keyword) {
            $query->where('name|slug', 'like', "%{$keyword}%");
        }
        if ($status !== '') {
            $query->where('status', $status);
        }

        return $this->paginate($query, $params['page'] ?? 1, $params['limit'] ?? 100);
    }

    public function tree(): array
    {
        $categories = db('category')->order('sort', 'asc')->select()->toArray();
        return $this->buildTree($categories);
    }

    public function create(array $data): array
    {
        if (empty($data['name'])) {
            return $this->error('分类名称不能为空');
        }

        $data['slug'] = $data['slug'] ?? str_slug($data['name']);

        $category = new CategoryModel();
        $category->save($data);

        return $this->success(['id' => $category->id], '添加成功');
    }

    public function update(int $id, array $data): array
    {
        try {
            $category = $this->findOrFail($id);
        } catch (\RuntimeException) {
            return $this->error('分类不存在');
        }

        if (empty($data['slug'])) {
            $data['slug'] = str_slug($data['name']);
        }
        unset($data['id']);

        $category->save($data);

        return $this->success(null, '更新成功');
    }

    public function delete(int $id): array
    {
        $hasChild = $this->count(['pid' => $id]);
        if ($hasChild > 0) {
            return $this->error('请先删除子分类');
        }

        return parent::delete($id) ? $this->success(null, '删除成功') : $this->error('删除失败');
    }

    public function getOptions(): array
    {
        return db('category')->where('status', 1)->order('sort', 'asc')->field('id, pid, name')->select()->toArray();
    }

    private function buildTree(array $data, int $pid = 0, string $pk = 'id', string $child = 'children'): array
    {
        $tree = [];
        foreach ($data as $item) {
            if ($item[$pk] == $pid) {
                $children = $this->buildTree($data, $item[$pk], $pk, $child);
                if ($children) {
                    $item[$child] = $children;
                }
                $tree[] = $item;
            }
        }
        return $tree;
    }
}