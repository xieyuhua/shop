<?php
declare(strict_types=1);

namespace app\service\admin;

use app\entity\CategoryEntity;
use think\Paginator;

class CategoryService extends \app\service\Service
{
    protected $model = 'admin.Category';

    public function getList(int $page = 1, int $limit = 100): Paginator
    {
        return CategoryEntity::order('sort', 'asc')->paginate([
            'page' => $page,
            'list_rows' => $limit,
        ]);
    }

    public function getTreeList(): array
    {
        $list = CategoryEntity::order('sort', 'asc')->select()->toArray();
        return $this->buildTree($list);
    }

    public function getOptions(): array
    {
        $list = CategoryEntity::where('status', 1)->order('sort', 'asc')->select()->toArray();
        return $this->buildOptions($list, 0);
    }

    public function create(array $data): array
    {
        if (empty($data['name'])) {
            return $this->error('分类名称不能为空');
        }
        
        $data['slug'] = $data['slug'] ?: str_slug($data['name']);
        
        $category = new CategoryEntity();
        $category->save($data);
        
        return $this->result($category, '添加成功');
    }

    public function update(int $id, array $data): array
    {
        $category = CategoryEntity::find($id);
        if (!$category) {
            return $this->error('记录不存在');
        }
        
        if (empty($data['slug'])) {
            $data['slug'] = str_slug($data['name']);
        }
        
        $category->save($data);
        
        return $this->result($category, '更新成功');
    }

    public function delete(int $id): array
    {
        $category = CategoryEntity::find($id);
        if (!$category) {
            return $this->error('记录不存在');
        }
        
        if (CategoryEntity::where('pid', $id)->count() > 0) {
            return $this->error('请先删除子分类');
        }
        
        if (\app\entity\ProductEntity::where('category_id', $id)->count() > 0) {
            return $this->error('该分类下有商品，无法删除');
        }
        
        $category->delete();
        
        return $this->result(null, '删除成功');
    }

    public function setStatus(int $id, int $status): array
    {
        $category = CategoryEntity::find($id);
        if (!$category) {
            return $this->error('记录不存在');
        }
        
        $category->save(['status' => $status]);
        
        return $this->result(null, '操作成功');
    }

    protected function buildTree(array $list, int $pid = 0, int $level = 0): array
    {
        $tree = [];
        foreach ($list as $item) {
            if ($item['pid'] == $pid) {
                $item['level'] = $level;
                $item['children'] = $this->buildTree($list, $item['id'], $level + 1);
                $tree[] = $item;
            }
        }
        return $tree;
    }

    protected function buildOptions(array $list, int $pid = 0, int $level = 0): array
    {
        $options = [];
        foreach ($list as $item) {
            if ($item['pid'] == $pid) {
                $prefix = str_repeat('└─ ', $level);
                $options[$item['id']] = $prefix . $item['name'];
                $children = $this->buildOptions($list, $item['id'], $level + 1);
                $options = $options + $children;
            }
        }
        return $options;
    }
}