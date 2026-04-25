<?php
declare(strict_types=1);

namespace app\model\admin;

use think\Model;

/**
 * @property int $id
 * @property int $pid
 * @property string $name
 * @property string $slug
 * @property string|null $image
 * @property string|null $description
 * @property int $sort
 * @property int $status
 * @property string $create_time
 * @property string|null $update_time
 */
class CategoryModel extends Model
{
    protected $name = 'category';
    protected $pk = 'id';
    protected $autoWriteTimestamp = 'datetime';
    protected $updateTime = 'update_time';
    protected $createTime = 'create_time';

    public function isTopLevel(): bool
    {
        return $this->pid === 0;
    }

    public function isActive(): bool
    {
        return $this->status === 1;
    }

    public static function getTreeList(): array
    {
        $list = self::where('status', 1)->order('sort', 'asc')->select()->toArray();
        return self::buildTree($list);
    }

    protected static function buildTree(array $items, int $pid = 0): array
    {
        $tree = [];
        foreach ($items as $item) {
            if ($item['pid'] == $pid) {
                $item['children'] = self::buildTree($items, $item['id']);
                $tree[] = $item;
            }
        }
        return $tree;
    }
}