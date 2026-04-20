<?php

declare(strict_types=1);

namespace app\common\entity;

use app\common\model\Category;

/**
 * 分类实体
 */
class CategoryEntity extends BaseEntity
{
    protected $table = 'category';

    protected $type = [
        'sort' => 'integer',
        'pid' => 'integer',
        'is_show' => 'integer',
    ];

    // ========== 业务逻辑 ==========

    /**
     * 获取分类列表
     */
    public function getList(int $pid = 0, bool $withHidden = false): array
    {
        $query = self::order('sort', 'asc');

        if (!$withHidden) {
            $query->where('is_show', 1);
        }

        if ($pid >= 0) {
            $query->where('pid', $pid);
        }

        return $query->select()->toArray();
    }

    /**
     * 获取分类树
     */
    public function getTree(): array
    {
        return Category::getTree();
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
     * 获取所有子分类ID
     */
    public function getChildIds(int $pid): array
    {
        $ids = self::where('pid', $pid)->column('id') ?? [];
        foreach ($ids as $id) {
            $ids = array_merge($ids, $this->getChildIds($id));
        }
        return $ids;
    }

    /**
     * 获取分类面包屑
     */
    public function getBreadcrumb(int $id): array
    {
        $breadcrumb = [];
        $category = self::find($id);

        while ($category) {
            array_unshift($breadcrumb, [
                'id' => $category->id,
                'name' => $category->name,
            ]);
            $category = $category->parent;
        }

        return $breadcrumb;
    }
}
