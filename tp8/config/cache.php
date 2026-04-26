<?php

return [
    'default' => env('CACHE_DRIVER', 'file'),

    'stores'  => [
        'file' => [
            'type'       => 'File',
            'path'       => '',
            'prefix'     => env('CACHE_PREFIX', 'tp8_'),
            'expire'     => env('CACHE_EXPIRE', 3600),
            'tag_prefix' => 'tag:',
            'serialize'  => [],
        ],
        
        'redis' => [
            'type'       => 'Redis',
            'host'       => env('REDIS_HOST', '127.0.0.1'),
            'port'       => env('REDIS_PORT', 6379),
            'password'  => env('REDIS_PASSWORD', ''),
            'select'     => env('REDIS_SELECT', 0),
            'timeout'   => env('REDIS_TIMEOUT', 0),
            'expire'    => env('CACHE_EXPIRE', 3600),
            'persistent' => false,
            'prefix'    => env('CACHE_PREFIX', 'tp8_'),
            'serialize' => ['serialize', 'unserialize'],
        ],
    ],
];