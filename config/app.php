<?php

$local = file_exists(__DIR__ . '/local.php') ? require(__DIR__ . '/local.php') : [];
$params = file_exists(__DIR__ . '/params.php') ? require(__DIR__ . '/params.php') : [];
$db = file_exists(__DIR__ . '/db.php') ? require(__DIR__ . '/db.php') : [
    'host' => null,
    'port' => null,
    'dbname' => null,
    'username' => null,
    'password' => null,
    'tablePrefix' => null,
];
if (isset($local['queue'])) {
    $local['queue3'] = $local['queue'];
    $local['queue3']['channel'] = $local['queue']['channel'] . '_other';
}

$config = [
    'id' => 'zjhj_mall_v4',
    'basePath' => dirname(__DIR__),
    'language' => 'zh-CN',
    'timeZone' => 'Asia/Shanghai',
    'bootstrap' => ['log', 'queue', 'queue3'],
    'components' => [
        'cache' => isset($local['cache']) ? $local['cache'] : [
            'class' => 'yii\caching\FileCache',
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=' . $db['host'] . ';port=' . $db['port'] . ';dbname=' . $db['dbname'],
            'username' => $db['username'],
            'password' => $db['password'],
            'tablePrefix' => $db['tablePrefix'],
            'charset' => 'utf8mb4',
            'attributes' => [
                // Windows 环境下貌似无效?
                // PDO::ATTR_EMULATE_PREPARES => false,
                // PDO::ATTR_STRINGIFY_FETCHES => false,
            ],
            'enableSchemaCache' => isset($local['enableSchemaCache']) ? $local['enableSchemaCache'] : false,
            // Duration of schema cache.
            'schemaCacheDuration' => isset($local['schemaCacheDuration']) ? $local['schemaCacheDuration'] : 3600,
            // Name of the cache component used to store schema information
            'schemaCache' => isset($local['schemaCache']) ? $local['schemaCache'] : 'cache',
            'on afterOpen' => function ($event) {
                Yii::$app->db->createCommand(
                    "SET SESSION sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'"
                )->execute();
            },
        ],
        'log' => isset($local['log']) ? $local['log'] : [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning',],
                    'logVars' => ['_GET', '_POST', '_FILES',],
                ],
            ],
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@app/core/mail',
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.qq.com',
                'port' => '465',
                'encryption' => 'ssl',
            ],
            'messageConfig' => [
                'charset' => 'UTF-8',
            ],
        ],
        'plugin' => [
            'class' => '\app\core\Plugin',
        ],
        'mutex' => [
            'class' => \yii\mutex\MysqlMutex::class,
        ],
        'queue' => isset($local['queue']) ? $local['queue'] : [
            'class' => \yii\queue\db\Queue::class,
            'tableName' => '{{%core_queue}}',
        ],
        'queue3' => isset($local['queue3']) ? $local['queue3'] : [
            'class' => \yii\queue\db\Queue::class,
            'tableName' => '{{%core_queue}}',
        ],
        'serializer' => [
            'class' => '\app\core\Serializer',
        ],
        'session' => isset($local['session']) ? $local['session'] : [
            'name' => 'HJ_SESSION_ID',
            'class' => 'yii\web\DbSession',
            'sessionTable' => '{{%core_session}}',
        ],
        'user' => [
            'class' => 'yii\web\User',
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
    ],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
    ],
    'params' => $params,
    'modules' => [
    ],
];
if (!$db['username']) {
    unset($config['components']['session']);
}
if (!empty($local['redis'])) {
    $config['components']['redis'] = $local['redis'];
}
return $config;
