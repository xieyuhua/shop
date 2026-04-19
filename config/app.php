<?php

use app\common\library\Config;

// 视图输出字符串替换
Config::viewReplaceStr([
    '__ADMIN__' => '/static/admin',
    '__HOME__' => '/static/home',
]);

return [
    // 默认应用
    'default_app' => 'api',

    // 默认时区
    'default_timezone' => 'Asia/Shanghai',

    // 应用命名空间
    'app_namespace' => 'app',

    // 开启路由
    'url_route_on' => true,

    // 路由配置文件
    'route_config_file' => ['route'],

    // 强制路由
    'url_route_must' => false,

    // 隐藏入口文件
    'url_domain_deploy' => false,

    // 域名路由
    'url_domain_root' => '',

    // 是否自动转换控制器和操作的命名
    'url_convert' => true,

    // 路由访问分隔符
    'url_controller_layer' => 'controller',

    // 默认验证器
    'default_validate' => '',

    // 默认JSONP渲染器
    'default_jsonp_handler' => 'jsonp',

    // 默认AJAX数据返回格式
    'default_ajax_return' => 'json',

    // 默认JSONP返回参数
    'jsonp_handler' => 'callback',

    // 异常处理
    'exception_handle' => '',

    // 错误显示
    'show_error_msg' => true,

    // 显示详细错误信息
    'show_detail_error_msg' => true,

    // 异常抛出
    'exception_toggle' => true,

    // 自动多应用模式
    'auto_multi_app' => true,

    // 应用映射
    'app_map' => [],

    // 域名映射
    'domain_bind' => [],

    // 禁止访问
    'deny_vist' => [],
];
