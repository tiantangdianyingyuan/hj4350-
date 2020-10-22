<?php

$local = file_exists(__DIR__ . '/local.php') ? require(__DIR__ . '/local.php') : [];
$config = require __DIR__ . '/app.php';
$config['controllerNamespace'] = 'app\commands';
$config['components']['request'] = [
    'class' => \app\core\ConsoleRequest::class,
];

return $config;
