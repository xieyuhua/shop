<?php

// 助手函数文件

/**
 * 加密手机号
 */
if (!function_exists('encrypt_mobile')) {
    function encrypt_mobile($mobile)
    {
        if (empty($mobile)) return '';
        return substr($mobile, 0, 3) . '****' . substr($mobile, -4);
    }
}

/**
 * 解密手机号
 */
if (!function_exists('decrypt_mobile')) {
    function decrypt_mobile($mobile)
    {
        return $mobile;
    }
}

/**
 * 生成唯一订单号
 */
if (!function_exists('create_order_no')) {
    function create_order_no()
    {
        return date('YmdHis') . rand(100000, 999999);
    }
}

/**
 * 格式化金额
 */
if (!function_exists('format_money')) {
    function format_money($money, $decimals = 2)
    {
        return number_format($money, $decimals, '.', '');
    }
}

/**
 * 获取客户端IP
 */
if (!function_exists('get_client_ip')) {
    function get_client_ip($type = 0, $adv = true)
    {
        $type = $type ? 1 : 0;
        static $ip = null;
        if (null !== $ip) {
            return $ip[$type];
        }
        
        if ($adv) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $pos = array_search('unknown', $arr);
                if (false !== $pos) {
                    unset($arr[$pos]);
                }
                $ip_str = trim(current($arr));
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip_str = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $ip_str = $_SERVER['REMOTE_ADDR'];
            }
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip_str = $_SERVER['REMOTE_ADDR'];
        }
        
        $long = sprintf("%u", ip2long($ip_str));
        $ip = $long ? [$ip_str, $long] : ['0.0.0.0', 0];
        return $ip[$type];
    }
}

/**
 * 驼峰转下划线
 */
if (!function_exists('camel_to_underline')) {
    function camel_to_underline($str)
    {
        return strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $str));
    }
}

/**
 * 下划线转驼峰
 */
if (!function_exists('underline_to_camel')) {
    function underline_to_camel($str)
    {
        $str = str_replace(' ', '', ucwords(str_replace('_', ' ', $str)));
        return lcfirst($str);
    }
}

/**
 * 递归创建目录
 */
if (!function_exists('mkdirs')) {
    function mkdirs($dir, $mode = 0777)
    {
        if (is_dir($dir) || @mkdir($dir, $mode)) {
            return true;
        }
        if (!mkdirs(dirname($dir), $mode)) {
            return false;
        }
        return @mkdir($dir, $mode);
    }
}

/**
 * 复制目录
 */
if (!function_exists('copydirs')) {
    function copydirs($source, $dest)
    {
        if (!is_dir($dest)) {
            mkdirs($dest);
        }
        $handle = opendir($source);
        while (($item = readdir($handle)) !== false) {
            if ($item == '.' || $item == '..') continue;
            $_source = $source . '/' . $item;
            $_dest = $dest . '/' . $item;
            if (is_file($_source)) {
                copy($_source, $_dest);
            } elseif (is_dir($_source)) {
                copydirs($_source, $_dest);
            }
        }
        closedir($handle);
    }
}

/**
 * 递归删除目录
 */
if (!function_exists('removedirs')) {
    function removedirs($dir, $force = false)
    {
        if (!is_dir($dir)) {
            return false;
        }
        $handle = opendir($dir);
        while (($item = readdir($handle)) !== false) {
            if ($item == '.' || $item == '..') continue;
            $_dir = $dir . '/' . $item;
            if (is_dir($_dir)) {
                removedirs($_dir, $force);
            } elseif (!$force || @unlink($_dir)) {
                // 不强制删除
            }
        }
        closedir($handle);
        return @rmdir($dir);
    }
}

/**
 * 安全过滤HTML
 */
if (!function_exists('filter_html')) {
    function filter_html($html)
    {
        $html = strip_tags($html, '<p><br><a><img><ul><ol><li><h1><h2><h3><h4><h5><h6><table><tr><td><th><thead><tbody>');
        $html = preg_replace('/\s+/', ' ', $html);
        return $html;
    }
}

/**
 * 生成随机字符串
 */
if (!function_exists('random_string')) {
    function random_string($length = 16, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789')
    {
        $str = '';
        $max = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[mt_rand(0, $max)];
        }
        return $str;
    }
}

/**
 * 友好时间显示
 */
if (!function_exists('friend_time')) {
    function friend_time($time)
    {
        if (!$time) return '';
        $truetime = is_numeric($time) ? $time : strtotime($time);
        $diff = time() - $truetime;
        if ($diff > 31536000) {
            return date('Y-m-d', $truetime);
        } elseif ($diff > 2592000) {
            return floor($diff / 2592000) . '个月前';
        } elseif ($diff > 86400) {
            return floor($diff / 86400) . '天前';
        } elseif ($diff > 3600) {
            return floor($diff / 3600) . '小时前';
        } elseif ($diff > 60) {
            return floor($diff / 60) . '分钟前';
        } else {
            return '刚刚';
        }
    }
}

/**
 * XML转数组
 */
if (!function_exists('xml_to_array')) {
    function xml_to_array($xml)
    {
        $reg = "/<(\\w+)[^>]*?>([\\x00-\\xFF]*?)<\\/\\1>/";
        if (preg_match_all($reg, $xml, $matches)) {
            $count = count($matches);
            $arr = [];
            for ($i = 0; $i < $matches[0]; $i++) {
                $key = $matches[1][$i];
                $val = xml_to_array($matches[2][$i]);
                if (array_key_exists($key, $arr)) {
                    if (is_array($arr[$key])) {
                        $arr[$key][] = $val;
                    } else {
                        $arr[$key] = [$val];
                    }
                } else {
                    $arr[$key] = $val;
                }
            }
            return $arr;
        } else {
            return $xml;
        }
    }
}

/**
 * 数组转XML
 */
if (!function_exists('array_to_xml')) {
    function array_to_xml($arr, $root = 'xml')
    {
        $xml = "<{$root}>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<{$key}>{$val}</{$key}>";
            } elseif (is_array($val)) {
                $xml .= "<{$key}>" . array_to_xml($val) . "</{$key}>";
            } else {
                $xml .= "<{$key}><![CDATA[{$val}]]></{$key}>";
            }
        }
        $xml .= "</{$root}>";
        return $xml;
    }
}
