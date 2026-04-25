<?php
declare(strict_types=1);

namespace app\service\admin;

use app\entity\ConfigEntity;
use think\Paginator;

class ConfigService extends \app\service\Service
{
    protected $model = 'admin.Config';

    public function getList(string $group = 'basic'): Paginator
    {
        return ConfigEntity::where('group', $group)->order('id', 'asc')->paginate([
            'page' => 1,
            'list_rows' => 100,
        ]);
    }

    public function getConfig(string $name, string $default = ''): string
    {
        $config = ConfigEntity::where('name', $name)->find();
        return $config ? $config->value : $default;
    }

    public function setConfig(string $name, string $value): array
    {
        $config = ConfigEntity::where('name', $name)->find();
        if ($config) {
            $config->save(['value' => $value]);
        }
        return $this->result(null, '保存成功');
    }

    public function saveBatch(array $data): array
    {
        foreach ($data as $name => $value) {
            ConfigEntity::where('name', $name)->update(['value' => $value]);
        }
        return $this->result(null, '保存成功');
    }

    public function getGroupConfig(string $group): array
    {
        $list = ConfigEntity::where('group', $group)->select();
        $config = [];
        foreach ($list as $item) {
            $config[$item->name] = $item->value;
        }
        return $config;
    }
}