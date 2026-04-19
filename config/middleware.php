<?php

return [
    'alias' => [
        'Auth' => app\api\middleware\AuthMiddleware::class,
        'Cors' => app\api\middleware\CorsMiddleware::class,
    ],
    
    'priority' => [],
];
