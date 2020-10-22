<?php
/**
 * 项目本地配置
 */

return [
    'redis' => [
        'class' => 'yii\redis\Connection',
        'hostname' => '127.0.0.1',
        'port' => 6379,
    ],
    'cache' => [
        'class' => 'yii\caching\FileCache',
    ],
    'enableSchemaCache' => false,
    'schemaCacheDuration' => 3600,
    'schemaCache' => 'cache',
    'session' => [
        'name' => 'HJ_SESSION_ID',
        'class' => 'yii\web\DbSession',
        'sessionTable' => '{{%core_session}}',
    ],
    'debugAllowedIPs' => ['127.0.0.1', '::1',],
    'giiAllowedIPs' => ['127.0.0.1', '::1',],
    'queue' => [
        'class' => \yii\queue\db\Queue::class,
        'tableName' => '{{%core_queue}}',
    ],
];
