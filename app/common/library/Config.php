<?php

declare(strict_types=1);

namespace app\common\library;

use app\common\model\Setting;

class Config
{
    private static $config = [];

    public static function get($key, $default = null)
    {
        if (isset(self::$config[$key])) {
            return self::$config[$key];
        }

        $setting = Setting::where('key', $key)->find();
        if ($setting) {
            self::$config[$key] = $setting->value;
            return $setting->value;
        }

        return $default;
    }

    public static function set($key, $value)
    {
        $setting = Setting::where('key', $key)->find();
        if ($setting) {
            $setting->value = $value;
            $setting->save();
        } else {
            $setting = new Setting();
            $setting->key = $key;
            $setting->value = $value;
            $setting->save();
        }

        self::$config[$key] = $value;
        return true;
    }

    public static function getAll($group = '')
    {
        $query = Setting::select();
        if ($group) {
            $query->where('group', $group);
        }

        $settings = $query->select();
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting->key] = $setting->value;
        }

        return $result;
    }
}
