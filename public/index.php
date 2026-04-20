<?php

require __DIR__ . '/vendor/autoload.php';

$app = new app\Application();
$response = $app->run();
$response->send();
