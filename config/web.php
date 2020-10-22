<?php

$local = file_exists(__DIR__ . '/local.php') ? require(__DIR__ . '/local.php') : [];
$config = require __DIR__ . '/app.php';

$config['components']['errorHandler'] = [
    'class' => 'app\core\ErrorHandler',
];
$config['components']['request'] = [
    'cookieValidationKey' => 'JZVyijtbbpYd-m0OCXCXHsAzmRurIDaI',
];

if (YII_ENV_DEV) {
    // Yii2 Debug模块
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'panels' => [
            'queue' => \yii\queue\debug\Panel::class,
        ],
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => isset($local['debugAllowedIPs']) ? $local['debugAllowedIPs'] : ['127.0.0.1', '::1',],
    ];

    // Yii2 gii模块（脚手架）
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => isset($local['giiAllowedIPs']) ? $local['giiAllowedIPs'] : ['127.0.0.1', '::1'],
    ];
}

return $config;
