<?php
declare(strict_types=1);

namespace app\model\admin;

use think\Model;

/**
 * @property int $id
 * @property string $group
 * @property string $name
 * @property string|null $value
 * @property string|null $description
 * @property string $create_time
 * @property string|null $update_time
 */
class ConfigModel extends Model
{
    protected $name = 'config';
    protected $pk = 'id';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    public static function getValue(string $name, string $default = ''): string
    {
        $model = self::where('name', $name)->find();
        return $model ? $model->value : $default;
    }

    public static function setValue(string $name, string $value): bool
    {
        return self::where('name', $name)->update(['value' => $value]) !== false;
    }

    public static function getGroupConfig(string $group): array
    {
        $list = self::where('group', $group)->select();
        $data = [];
        foreach ($list as $item) {
            $data[$item->name] = $item->value;
        }
        return $data;
    }
}