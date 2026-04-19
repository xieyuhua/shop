<?php

return [
    'default_app' => 'api',
    'default_action' => 'index',
    'default_controller' => 'Index',
    'url_route_on' => true,
    'url_route_must' => false,
    'route_annotation' => false,
    'url_domain_deploy' => false,
    'url_domain_root' => '',
    'route_config_file' => [],
    'route_complete_match' => false,
    'route_rule_cache' => false,
    'route_time' => [
        'routeCheckCache' => false,
        'routeCheckCacheInterval' => 60,
    ],
    'pathinfo_fetch' => [
        'ORIG_PATH_INFO',
        'REDIRECT_PATH_INFO',
        'REDIRECT_URL',
    ],
    'pathinfo_var' => 's',
    'pathinfo_depr' => '/',
    'url_common_param' => false,
    'url_param_type' => 0,
    'url_lazy_route' => false,
    'url_route_on' => true,
    'url_route_not_check' => false,
    'route_annotation_on' => false,
];
