<?php
declare(strict_types=1);

namespace app\service;

use app\model\admin\ConfigModel;

class ConfigService extends Service
{
    protected string $model = 'config';

    public function index(string $group = 'basic'): array
    {
        $list = ConfigModel::where('group', $group)->select();
        return $this->success($list);
    }

    public function save(array $data): array
    {
        foreach ($data as $name => $value) {
            ConfigModel::setValue($name, $value);
        }
        return $this->success(null, '保存成功');
    }

    public function getValue(string $name, string $default = ''): string
    {
        return ConfigModel::getValue($name, $default);
    }

    public function getGroupConfig(string $group): array
    {
        return ConfigModel::getGroupConfig($group);
    }
}