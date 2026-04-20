<?php

declare(strict_types=1);

namespace app\common\entity;

use app\common\model\Category;
use app\common\model\Product;

/**
 * 后台分类实体
 */
class AdminCategoryEntity extends BaseEntity
{
    protected $table = 'category';

    protected $type = [
        'sort' => 'integer',
        'pid' => 'integer',
        'is_show' => 'integer',
        'is_nav' => 'integer',
    ];

    // ========== 业务逻辑 ==========

    /**
     * 获取分类列表
     */
    public function getList(): array
    {
        return self::order('sort', 'asc')->select()->toArray();
    }

    /**
     * 获取分类树
     */
    public function getTree(): array
    {
        return Category::getTree();
    }

    /**
     * 获取分类选项
     */
    public function getOptions(): array
    {
        return Category::getOptions();
    }

    /**
     * 获取分类详情
     */
    public function getDetail(int $id): ?array
    {
        $category = self::find($id);
        return $category ? $category->toArray() : null;
    }

    /**
     * 创建分类
     */
    public function create(array $data): array
    {
        if (empty($data['name'])) {
            return ['success' => false, 'msg' => '请输入分类名称'];
        }

        $category = new self();
        $category->pid = $data['pid'] ?? 0;
        $category->name = $data['name'];
        $category->icon = $data['icon'] ?? '';
        $category->image = $data['image'] ?? '';
        $category->sort = $data['sort'] ?? 100;
        $category->is_show = $data['is_show'] ?? 1;
        $category->is_nav = $data['is_nav'] ?? 0;
        $category->save();

        return ['success' => true, 'data' => $category->toArray()];
    }

    /**
     * 更新分类
     */
    public function update(array $data): array
    {
        if (empty($data['id'])) {
            return ['success' => false, 'msg' => '分类ID不能为空'];
        }

        $category = self::find($data['id']);
        if (!$category) {
            return ['success' => false, 'msg' => '分类不存在'];
        }

        $fields = ['name', 'pid', 'icon', 'image', 'sort', 'is_show', 'is_nav'];
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $category->$field = $data[$field];
            }
        }
        $category->save();

        return ['success' => true, 'data' => $category->toArray()];
    }

    /**
     * 删除分类
     */
    public function delete(int $id): array
    {
        $category = self::find($id);
        if (!$category) {
            return ['success' => false, 'msg' => '分类不存在'];
        }

        $hasChildren = self::where('pid', $id)->count();
        if ($hasChildren > 0) {
            return ['success' => false, 'msg' => '请先删除子分类'];
        }

        $hasProducts = Product::where('category_id', $id)->count();
        if ($hasProducts > 0) {
            return ['success' => false, 'msg' => '该分类下有商品，无法删除'];
        }

        $category->delete();

        return ['success' => true];
    }
}
