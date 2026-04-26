<?php
declare(strict_types=1);

namespace app\service;

class DictService extends Service
{
    protected string $model = 'dict';

    const CACHE_KEY = 'dict:';
    const CACHE_TTL = 3600;

    public function get(string $group): array
    {
        $cacheKey = self::CACHE_KEY . $group;
        $data = cache($cacheKey);
        
        if ($data) {
            return $data;
        }

        $list = db($this->model)->where('group', $group)->where('status', 1)->order('sort', 'asc')->select();
        
        $items = [];
        foreach ($list as $item) {
            $items[$item['value']] = $item['label'];
        }
        
        cache($cacheKey, $items, self::CACHE_TTL);
        
        return $items;
    }

    public function getList(string $group): array
    {
        return db($this->model)->where('group', $group)->where('status', 1)->order('sort', 'asc')->select()->toArray();
    }

    public function getOptions(string $group): array
    {
        $list = $this->getList($group);
        return array_map(fn($item) => ['value' => $item['value'], 'label' => $item['label']], $list);
    }

    public function refresh(string $group = ''): void
    {
        if ($group) {
            cache(self::CACHE_KEY . $group, null);
        } else {
            $groups = db($this->model)->column('group');
            foreach ($groups as $g) {
                cache(self::CACHE_KEY . $g, null);
            }
        }
    }

    public function getStatusOptions(): array
    {
        return [
            ['value' => 1, 'label' => '启用'],
            ['value' => 0, 'label' => '禁用'],
        ];
    }

    public function getGenderOptions(): array
    {
        return [
            ['value' => 0, 'label' => '未知'],
            ['value' => 1, 'label' => '男'],
            ['value' => 2, 'label' => '女'],
        ];
    }

    public function getOrderStatusOptions(): array
    {
        return [
            ['value' => 0, 'label' => '待付款'],
            ['value' => 1, 'label' => '待发货'],
            ['value' => 2, 'label' => '待收货'],
            ['value' => 3, 'label' => '已完成'],
            ['value' => 4, 'label' => '已取消'],
            ['value' => 5, 'label' => '已退款'],
        ];
    }

    public function getPayTypeOptions(): array
    {
        return [
            ['value' => 1, 'label' => '微信支付'],
            ['value' => 2, 'label' => '支付宝'],
            ['value' => 3, 'label' => '余额支付'],
        ];
    }
}