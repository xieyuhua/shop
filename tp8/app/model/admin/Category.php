<?php
declare(strict_types=1);

namespace app\model\admin;

use think\Model;

class Category extends Model
{
    protected $name = 'category';
    protected $pk = 'id';
    protected $autoWriteTimestamp = 'datetime';
    protected $updateTime = 'update_time';
    protected $createTime = 'create_time';

    public static function getTreeOptions($pid = 0, $level = 0)
    {
        $list = self::where('pid', $pid)->where('status', 1)->order('sort', 'asc')->select();
        $options = [];
        foreach ($list as $item) {
            $options[$item['id']] = str_repeat('└─ ', $level) . $item['name'];
            $children = self::getTreeOptions($item['id'], $level + 1);
            $options = $options + $children;
        }
        return $options;
    }
}