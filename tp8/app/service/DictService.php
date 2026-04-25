<?php
declare(strict_types=1);

namespace app\service;

use think\facade\Db;

class DictService
{
    const CACHE_KEY = 'dict:';
    const CACHE_TTL = 3600;

    public static function get(string $group): array
    {
        $cacheKey = self::CACHE_KEY . $group;
        $data = cache($cacheKey);
        
        if ($data) {
            return $data;
        }

        $list = Db::name('dict')->where('group', $group)->where('status', 1)->order('sort', 'asc')->select();
        
        $items = [];
        foreach ($list as $item) {
            $items[$item['value']] = $item['label'];
        }
        
        cache($cacheKey, $items, self::CACHE_TTL);
        
        return $items;
    }

    public static function getList(string $group): array
    {
        return Db::name('dict')->where('group', $group)->where('status', 1)->order('sort', 'asc')->select();
    }

    public static function getOptions(string $group): array
    {
        $list = self::getList($group);
        $options = [];
        foreach ($list as $item) {
            $options[] = ['value' => $item['value'], 'label' => $item['label']];
        }
        return $options;
    }

    public static function refresh(string $group = ''): void
    {
        if ($group) {
            cache(self::CACHE_KEY . $group, null);
        } else {
            $groups = Db::name('dict')->column('group');
            foreach ($groups as $g) {
                cache(self::CACHE_KEY . $g, null);
            }
        }
    }

    public static function getStatusOptions(): array
    {
        return [
            ['value' => 1, 'label' => '启用'],
            ['value' => 0, 'label' => '禁用'],
        ];
    }

    public static function getGenderOptions(): array
    {
        return [
            ['value' => 0, 'label' => '未知'],
            ['value' => 1, 'label' => '男'],
            ['value' => 2, 'label' => '女'],
        ];
    }

    public static function getOrderStatusOptions(): array
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

    public static function getPayTypeOptions(): array
    {
        return [
            ['value' => 1, 'label' => '微信支付'],
            ['value' => 2, 'label' => '支付宝'],
            ['value' => 3, 'label' => '余额支付'],
        ];
    }
}