<?php

declare(strict_types=1);

namespace app\common\model;

use think\Model;

class Category extends Model
{
    protected $table = 'category';

    protected $type = [
        'pid' => 'integer',
        'name' => 'string',
        'icon' => 'string',
        'image' => 'string',
        'sort' => 'integer',
        'is_show' => 'integer',
        'is_nav' => 'integer',
    ];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'pid');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'pid');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function shops()
    {
        return $this->hasMany(Shop::class, 'category_id');
    }

    public static function getTree($pid = 0)
    {
        $list = self::where('pid', $pid)
            ->order('sort', 'asc')
            ->select();

        foreach ($list as &$item) {
            $children = self::getTree($item->id);
            if (!empty($children)) {
                $item->children = $children;
            }
        }

        return $list;
    }

    public static function getOptions($pid = 0, $prefix = '')
    {
        $list = self::where('pid', $pid)
            ->where('is_show', 1)
            ->order('sort', 'asc')
            ->select();

        $options = [];
        foreach ($list as $item) {
            $options[] = [
                'id' => $item->id,
                'name' => $prefix . $item->name,
            ];
            $children = self::getOptions($item->id, $prefix . '　　');
            $options = array_merge($options, $children);
        }

        return $options;
    }
}
