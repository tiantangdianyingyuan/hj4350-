<?php
return [
    'default' => 'wechat',

    'drivers' => [
        'wechat' => [
            'appId' => 'your appid',
            'appSecret' => 'your app secret',

            'deliveryId' => '',
            'shopId' => '',
            'deliveryAppSecret' => 'delivery app secret',
        ],
        'dada' => [
            'appKey' => 'your appKey',
            'appSecret' => 'your app secret',
            'sourceId' => '',
            'debug' => true, // 是否测试
        ],
        'sf' => [
            'dev_id' => '',
            'dev_key' => '',
            'shop_id' => '',
        ],
        'ss' => [
            'clientId' => '',
            'secret' => '',
            'shopId' => '',
            'debug' => false, // 是否开启调试
        ],
        'mt' => [
            'appKey' => '',
            'appSecret' => '',
            'shopId' => '',
        ],
    ],
];
