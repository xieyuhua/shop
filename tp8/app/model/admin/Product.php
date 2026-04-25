<?php
declare(strict_types=1);

namespace app\model\admin;

use think\Model;

class Product extends Model
{
    protected $name = 'product';
    protected $pk = 'id';
    protected $autoWriteTimestamp = 'datetime';
    protected $updateTime = 'update_time';
    protected $createTime = 'create_time';

    public function category()
    {
        return $this->belongsTo('Category', 'category_id');
    }

    protected function getImagesAttr($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    protected function setImagesAttr($value)
    {
        return is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
    }

    protected function getSpecsAttr($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    protected function setSpecsAttr($value)
    {
        return is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
    }
}